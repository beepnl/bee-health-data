<?php

namespace App\Http\Controllers;

use App\Http\Requests\MembershipRequest;
use App\Models\Organisation;
use App\Models\RegistrationInvitation;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class MembershipController extends Controller
{
    const USERS_PER_PAGE = 10;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(MembershipRequest $request)
    {
        Gate::authorize('isAdmin');

        $current_page = LengthAwarePaginator::resolveCurrentPage();
        $per_page = self::USERS_PER_PAGE;
        $offset = ($current_page * $per_page) - $per_page;
        $admin_organisation_ids = $request->user()->admin_organisations->pluck('id');

        $register_invitations = DB::table('registration_invitation')
        ->leftJoin('organisation', 'organisation.id', '=', 'registration_invitation.organisation_id')
        ->leftJoin('user_role', 'user_role.id', '=', 'registration_invitation.user_role_id')
        ->whereNotExists(function($query){
            $query->select(DB::raw(1))
                ->from('users')
                ->join('organisation_user', 'organisation_user.organisation_id', '=', 'registration_invitation.organisation_id')
                ->whereColumn('registration_invitation.email', 'users.email');
        })
        ->select('registration_invitation.id', 'registration_invitation.email', 'registration_invitation.email as fullname', 'user_role.name as user_role', 'organisation.name as organisation_name', DB::raw('\'invitation sent\' as status'), 'registration_invitation.updated_at', 'organisation.id as organisation_id', 'user_role.id as user_role_id');

        if (!$request->user()->isSuperAdmin()) {
            $register_invitations = $register_invitations->whereIn('organisation.id', $admin_organisation_ids);
        }

        $records = DB::table('users')
        ->leftJoin('organisation_user', 'organisation_user.user_id', '=', 'users.id')
        ->leftJoin('organisation', 'organisation.id', '=', 'organisation_user.organisation_id')
        ->leftJoin('user_role', 'user_role.id', '=', 'organisation_user.user_role')
        ->union($register_invitations)
        ->select('users.id', 'users.email', DB::raw('trim(concat(firstname, \' \', lastname)) as fullname'), 'user_role.name as user_role', 'organisation.name as organisation_name',
        DB::raw('case when length(firstname) > 0 AND length(lastname) > 0 AND length(password) > 0 AND length(accepted_terms_and_conditions) > 0 then \'active\' else \'awaiting activation\' end as status'),
            'users.updated_at', 'organisation.id as organisation_id', 'user_role.id as user_role_id')
        ->where('user_role', '!=', null);
        
        if($request->has('sort')){
            $records = $records->orderBy($request->sort, $request->direction);
        }
        
        if($request->user()->isSuperAdmin()){
            $users = $records->get();
        }else{
            $users = $records->whereIn('organisation.id', $admin_organisation_ids)->get();
        }

        $page_users = $records->limit($per_page)->offset($offset)->get();

        $status = explode(',', $request->input('status'));
        $status = array_filter($status, function($s){
            return $s != "";
        });
        if (!empty($status)) {
            $users = $users->whereIn('status', $status);
            $page_users = $page_users->whereIn('status', $status);
        }
        
        $paginate_records = (new LengthAwarePaginator($page_users, $users->count(), $per_page, $current_page))->withPath('');

        return view('members.index', ['users' => $paginate_records]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        Gate::authorize('isAdmin');

        $user_roles = UserRole::all(['id', 'name']);

        if (Auth::user()->isSuperAdmin()) {
            $organisations = Organisation::all(['id', 'name']);
        } else {
            $organisations = Auth::user()->admin_organisations->all('id', 'name');
            $user_roles = $user_roles
                ->filter(function ($role) {
                /**
                 * https://github.com/beepnl/bee-health-data-portal/issues/4
                 * Invite new user, see https://xd.adobe.com/view/1bf60d4b-3beb-4518-816d-9fa29c5598a9-4ea5/screen/1f127f02-622d-4444-a1a9-eb89c9768827
                 */
                    return true;
                    // return $role->name === UserRole::USER;
                })
                ->map(function($role) {
                    $role->is_selected = UserRole::USER === $role->name;
                    return $role;
                });
        }

        return view('members.create', compact('user_roles', 'organisations'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(MembershipRequest $request)
    {
        $organisation = Organisation::find($request->organisation_id);
        Gate::authorize('isAdminOf', $organisation);
        
        $request->request->add(['name' => Auth::user()->append('fullname')->fullname]);
        RegistrationInvitation::create($request->all());
        return redirect('account/members')->with('status', __('messages.invite', ['email' => $request->email, 'organisation' => $organisation->name]));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(MembershipRequest $request, User $user)
    {
        $organisation = $user->organisation($request->organisation_id)->firstOrFail();
        Gate::authorize('isAdminOf', $organisation);

        $selectedRoleId = $organisation->pivot->user_role;
        $roles = UserRole::all()->map(function($role) use ($selectedRoleId){
            $role->is_selected = $selectedRoleId === $role->id;
            return $role;
        });

        return view('members.edit', compact(['user', 'organisation', 'roles']));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(MembershipRequest $request, User $user)
    {
        $organisation = Organisation::findOrFail($request->organisation_id);
        Gate::authorize('isAdminOf', $organisation);

        if($organisation->canUpdateMember($user) || Gate::authorize('isAdmin')){
            $user->organisations()->updateExistingPivot($request->organisation_id, ['user_role' => $request->user_role_id]);
            return redirect('account/members');
        }else{
            return redirect('account/members')->with('warning', Lang::get('Can not update (:email) because is the only one with role (:role) ', ['email' => $user->email, 'role' => UserRole::ORGANISATION_ADMIN]));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(MembershipRequest $request, User $user)
    {
        $organisation = Organisation::findOrFail($request->organisation_id);
        Gate::authorize('isAdminOf', $organisation);
        if ($organisation->canUpdateMember($user) && $user->isAdminMemberOf($organisation)) {
            $user->delete();
        }else if($user->isUserMemberOf($organisation) || Gate::authorize('isAdmin')){
            $user->delete();
        }else{
            return redirect('account/members')->with('warning', Lang::get('Can not delete (:email) because is the only one with role (:role) ', ['email' => $user->email, 'role' => $organisation->users->find($user->id)->role->name]));
        }

        return redirect()->back();
    }
}
