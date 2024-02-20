<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegistrationInvitationRequest;
use App\Models\Organisation;
use Illuminate\Http\Request;
use App\Models\RegistrationInvitation;
use App\Models\UserRole;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Carbon;


class RegistrationInvitationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(RegistrationInvitationRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(RegistrationInvitation $registrationInvitation)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(RegistrationInvitation $registrationInvitation)
    {
        $organisation = Organisation::find($registrationInvitation->organisation_id);
        Gate::authorize('isAdminOf', $organisation);

        $selectedRoleId = $registrationInvitation->user_role_id;
        if (Auth::user()->isSuperAdmin()) {
            $roles = UserRole::all()->map(function ($role) use ($selectedRoleId) {
                $role->is_selected = $selectedRoleId === $role->id;
                return $role;
            });
        }else{
            $roles = UserRole::ofRole(UserRole::USER)->get()->map(function ($role) {
                $role->is_selected = UserRole::USER === $role->name;
                return $role;
            });
        }
        
        $register_invitations = $registrationInvitation;
        return view('invitation.edit', compact(['register_invitations', 'organisation', 'roles']));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(RegistrationInvitationRequest $request, RegistrationInvitation $registrationInvitation)
    {
        $organisation = Organisation::findOrFail($request->organisation_id);
        Gate::authorize('isAdminOf', $organisation);
        
        if ($request->has('resend')) {
            $username = Auth::user()->append('fullname')->fullname;
            $registrationInvitation->update(['name' => $username, 'expires_at' => Carbon::now()->addMinutes(config('auth.membership_invitation_expires_after'))]);
            return redirect('account/members')->with('status', Lang::get('Re-invite (:email)', ['email' => $registrationInvitation->email]));
        }

        $registrationInvitation->update(['user_role_id' => $request->user_role_id]);
        return redirect('account/members');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(RegistrationInvitation $registrationInvitation)
    {
        $organisation = Organisation::findOrFail($registrationInvitation->organisation_id);
        Gate::authorize('isAdminOf', $organisation);
        $registrationInvitation->delete();
        return redirect('account/members');
    }
}
