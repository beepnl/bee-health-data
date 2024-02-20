<?php

namespace App\Http\Controllers;

use App\Models\AuthorizationRequest;
use App\Notifications\AccessRequestNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class MyAuthorizationRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $my_access_requests = AuthorizationRequest::own()->latest()->get();
        return view('my_access_request.index', compact('my_access_requests'));
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
    public function store(Request $request)
    {
        //
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
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AuthorizationRequest $authorizationRequest)
    {
        if($authorizationRequest->is_approved){
            return redirect()->back();
        }
        // resend
        $dataset = $authorizationRequest->requesting_dataset;
        $users = $dataset->organisation->admin_users;
        $emails = $users->reduce(function($carry, $user){ return ($carry ? ($carry . ', ') : '') . $user->email; }, '');
        Notification::send($users, new AccessRequestNotification($dataset));
        return redirect()->back()->with('status', __('messages.resend_request', ['email' => $emails, 'dataset' => $dataset->name]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(AuthorizationRequest $authorizationRequest)
    {
        $authorizationRequest->authorization()->delete();
        $authorizationRequest->delete();
        return redirect()->route('my_access_requests.index');
    }
}
