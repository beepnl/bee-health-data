<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccountActivateRequest;
use App\Http\Requests\PostAccountRequest;
use App\Jobs\ProcessAccountDeactivation;
use App\Models\RegistrationInvitation;
use App\Models\TermsAndConditions;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('account.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showActivateForm(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->organisations;

        if ($request->route()->getName() === 'account.activate' && $user->account_is_activated) {
            return redirect()->route('login');
        }

        return view('account.profile.edit', $user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function activate(AccountActivateRequest $request, $id)
    {
        
        $user = User::findOrFail($id);
        
        if($request->route()->getName() === 'account.activate.post' && $user->account_is_activated){
            return redirect()->route('login');
        }
        $termsAndConditions = (new TermsAndConditions())->latest()->firstOrFail();
        $user->update(array_merge(
            $request->except(['password', 'password_confirmation', 'old_password', 'accepted_terms_and_conditions']),
            [
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,
                'accepted_terms_and_conditions' => $termsAndConditions->content,
                'password' => Hash::make($request->password),
                'is_active' => true
            ]
        ));

        // RegistrationInvitation::ofEmail($user->email)->delete();

        if(!$user->account_is_activated){
            return redirect()->route('login');
        }
        return redirect()->route('home', [$id]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(PostAccountRequest $request, User $user)
    {
        $data = [
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
        ];
        if($request->filled('old_password')){
            $user->update([
                'password' => Hash::make($request->password)
            ] + $data);
        }else{
            $user->update($data);
        }
        return redirect()->route('account.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit()
    {
        $user = Auth::user();
        $user->organisations;
        return view('account.profile.edit', $user);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        ProcessAccountDeactivation::dispatch($user)->delay(Carbon::now()->addMinutes(config('auth.deactivation_account_expires_after')));
        return redirect()->route('account.index')->with('status', 'Your request for remove account is computed and will be remove soon!');
    }
}
