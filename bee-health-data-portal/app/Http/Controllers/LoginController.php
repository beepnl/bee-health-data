<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostResetPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\User;
use App\Notifications\ResetPassword;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    public $redirectTo = "/login";

    public function showLoginForm(Request $request)
    {
        if ($request->user()) {
            return redirect('/');
        }
        return view('auth.login');
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        $request->merge([
            $this->username() => Str::lower($request->input($this->username())),
        ]);
        $this->validateLogin($request);

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if (
            method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)
        ) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    public function showForgotPassword()
    {
        return view('login.forgot_password');
    }

    public function forgotPassword(Request $request){
        $request->merge([
            $this->username() => Str::lower($request->input($this->username())),
        ]);
        $this->validateForgotPassword($request);

        $user = User::ofEmail($request->email)->first();
        if(is_null($user)){
            return redirect()->with('warning', __('messages.user_not_exists'));
        }
        if($user->is_active === false){
            return redirect()->route('account.activate', ['id' => $user->id])->with('warning', __('messages.user_not_active'));
        }

        $user->notify(new ResetPassword);

        return redirect()->to($this->redirectPath())->with('status', __('messages.forgot_password'));
    }

    public function showResetPassword(ResetPasswordRequest $request){
        Auth::logout();
        $user = User::findOrFail($request->id);
        Auth::login($user);

        return view('login.reset_password', ['id' => Auth::id()]);
    }

    public function resetPassword(PostResetPasswordRequest $request, $id){
        $user = User::findOrFail($id);
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return redirect($this->redirectTo)->with('status', __('messages.reset_password'));
    }

    protected function validateForgotPassword(Request $request)
    {
        $request->validate([
            $this->username() => 'required|email|exists:' . User::class . ',email'
        ]);
    }
}
