<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AccountDownloadController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\AuthorizationController;
use App\Http\Controllers\AuthorizationRequestController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DatasetController;
use App\Http\Controllers\FacetController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrganisationController;
use App\Http\Controllers\KeywordController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\MyAuthorizationRequestController;
use App\Http\Controllers\MyDatasetsController;
use App\Http\Controllers\NotificationsController;
use App\Http\Controllers\RegistrationInvitationController;
use App\Http\Controllers\TermsAndConditionsController;
use App\Http\Requests\InvitationVerificationRequest;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login/post', [LoginController::class, 'login'])->name('login.post');
Route::get('login/forgot', [LoginController::class, 'showForgotPassword'])->name('login.forgot');
Route::post('login/forgot/post', [LoginController::class, 'forgotPassword'])->name('login.forgot.post');
Route::get('login/reset/{id}/{hash}', [LoginController::class, 'showResetPassword'])->middleware(['signed'])->name('login.reset');
Route::put('login/reset/{id}', [LoginController::class, 'resetPassword'])->name('login.reset.post');
Route::get('logout', [LoginController::class, 'logout'])->name('logout');
Route::get('account/activate/{id}', [AccountController::class, 'showActivateForm'])->name('account.activate');
Route::put('account/activate/{id}', [AccountController::class, 'activate'])->name('account.activate.post');

Route::middleware(['auth'])->group(function () {
    Route::prefix('account')->group(function(){

        Route::get('/', [AccountController::class, 'index'])->name('account.index');
        Route::get('profile', [AccountController::class, 'edit'])->name('account.profile');
        Route::put('profile/{user}', [AccountController::class, 'update'])->name('account.update');
        Route::delete('profile/{user}', [AccountController::class, 'destroy'])->name('account.delete')->middleware('throttle:1,'.config('auth.deactivation_account_expires_after'));
        Route::resource('keywords', KeywordController::class);
        Route::resource('organisations', OrganisationController::class);
        Route::resource('members', MembershipController::class)->parameter('members', 'user');
        Route::resource('registration_invitations', RegistrationInvitationController::class);
        Route::resource('authorization_requests', AuthorizationRequestController::class);
        Route::resource('my_access_requests', MyAuthorizationRequestController::class)->parameter('my_access_requests', 'authorization_request');
        Route::get('my_datasets', [MyDatasetsController::class, 'index'])->name('my_datasets.index');

    });
    Route::get('notifications', [NotificationsController::class, 'index'])->name('notifications.index');
    Route::put('notifications', [NotificationsController::class, 'update'])->name('notifications.update');

    Route::post('authorization', [AuthorizationController::class, 'store']);
    Route::delete('authorization', [AuthorizationController::class, 'destroy']);
    Route::resource('authors', AuthorController::class);
    Route::get('download-account-data-export', [AccountDownloadController::class, 'export'])->middleware('throttle:1,1440');
});
Route::get('facet/{type}', [FacetController::class, 'search']);
Route::resource('files', FileController::class)->parameter('files', 'file_version');
Route::resource('datasets', DatasetController::class);


Route::get('/download-account-data/{id}/{hash}', [AccountDownloadController::class, 'download'])->middleware(['signed', 'throttle:10,1440'])->name('account.download');
Route::get('/invitation/verify/{id}/{hash}', function (InvitationVerificationRequest $request) {
    if(Auth::check()){
        Auth::logout();
    }
    $request->fulfill();
    return redirect()->route('account.activate', ['id' => $request->findOrCreate()]);
})->middleware(['signed'])->name('invitation.verify');
Route::get('contact', [ContactController::class, 'index']);
Route::post('contact/store', [ContactController::class, 'store'])->name('contact.store')->middleware('throttle:3,1');
Route::get('about', function(){
    return view('about.index');
});

Route::get('terms-and-conditions', [TermsAndConditionsController::class, 'show'])->name('terms-and-conditions.show');
Route::get('terms-and-conditions/edit', [TermsAndConditionsController::class, 'edit'])->name('terms-and-conditions.edit');
Route::post('terms-and-conditions', [TermsAndConditionsController::class, 'store'])->name('terms-and-conditions.store');
