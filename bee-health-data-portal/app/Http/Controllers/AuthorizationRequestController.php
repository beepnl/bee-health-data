<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthorizationRequestRequest;
use App\Models\AuthorizationRequest;
use App\Models\Dataset;
use App\Models\Organisation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class AuthorizationRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        
        if(Gate::allows('isSuperAdmin')){
            $organisation_ids = Organisation::get()->pluck('id');
        }else{
            $organisation_ids = Auth::user()->admin_organisations->pluck('id');
        }

        $records = DB::table('authorization_request')
            ->join('dataset', 'dataset.id', '=', 'authorization_request.requesting_dataset_id')
            ->join('organisation', 'organisation.id', '=', 'dataset.organisation_id')
            ->join('users', 'users.id', '=', 'authorization_request.requesting_user_id')
            ->leftJoin('organisation_user', 'organisation_user.user_id', '=', 'users.id')
            ->leftJoin('organisation as org', 'org.id', '=', 'organisation_user.organisation_id')
            ->select('authorization_request.id', 'authorization_request.requested_at', DB::raw('array_to_string(array_agg(org.name), \', \') as user_organisation'), 'organisation.name as organisation_request',  'dataset.name as dataset_name', DB::raw('trim(concat(firstname, \' \', lastname)) as fullname'))
            ->whereIn('dataset.publication_state', [Dataset::PUBLICATION_STATES_PUBLISHED, Dataset::PUBLICATION_STATES_DRAFT])
            ->whereIn('organisation.id', $organisation_ids)
            ->where('authorization_type', AuthorizationRequest::AUTHORIZATION_TYPE_USER_REQUESTS)
            ->groupBy(['authorization_request.id', 'authorization_request.requested_at', 'organisation.name', 'dataset.name', DB::raw('trim(concat(firstname, \' \', lastname))') ]);
 
        if ($request->has('sort')){
            $records = $records->orderBy($request->sort, $request->direction);
        }else{
            $records= $records->orderBy('requested_at', 'desc');
        }

        $records = $records->paginate(config('database.paginate'));
        return view('authorization_request.index', ['authorization_requests' => $records]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        // write to validate request
        $dataset = Dataset::findOrFail($request->dataset_id);
        
        // $dataset->has_access_request
        return view('authorization_request.create', compact('dataset'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(AuthorizationRequestRequest $request)
    {
        $dataset = Dataset::findOrFail($request->requesting_dataset_id);
        if($dataset->is_requested){
            return redirect()->route('datasets.show', ['dataset' => $dataset->id])->with('status', 'You have already requested this dataset successfully.');
        }

        $authorizationRequest = new AuthorizationRequest;
        $authorizationRequest->create([
            'reference' => $request->reference,
            'notes' => $request->notes,
            'requested_at' => Carbon::now(),
            'requesting_user_id' => Auth::id(),
            'requesting_organisation_id' => $request->input('requesting_organisation_id'),
            'requesting_dataset_id' => $request->input('requesting_dataset_id'),
        ]);

        return redirect()->route('datasets.show', ['dataset' => $dataset->id])->with('status', 'You requested this dataset successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(AuthorizationRequest $authorization_request)
    {
        return view('authorization_request.edit', compact('authorization_request'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(AuthorizationRequestRequest $request, AuthorizationRequest $authorization_request)
    {
        if(filter_var($request->is_approved, FILTER_VALIDATE_BOOL)){
            $authorization_request->approved_at = now();
            $authorization_request->rejected_at = null;
        }else{
            $authorization_request->rejected_at = now();
            $authorization_request->approved_at = null;

        }
        $authorization_request->response_note = $request->response_note;
        $authorization_request->save();


        return redirect()->route('authorization_requests.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
