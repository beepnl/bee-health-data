<?php

namespace App\Http\Requests;

use App\Models\Organisation;
use App\Models\RegistrationInvitation;
use App\Models\User;
use Exception;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class InvitationVerificationRequest extends FormRequest
{
    private $invitation = null;
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if(is_null($this->getInvitation())){
            return false;
        }

        if(! $this->getInvitation()->isNotExpired()){
            return false;
        }

        if (! hash_equals((string) $this->route('hash'), sha1($this->getInvitation()->getEmailForVerification()))) {
            return false;
        }

        return true;
    }

    public function rules()
    {
        return [];
    }

    public function getInvitation()
    {
        return $this->invitation ?: $this->invitation = RegistrationInvitation::find($this->route('id'));
    }

    public function findOrCreate()
    {
        if (!$user = User::ofEmail($this->getInvitation()->email)->first()) {
            $user = new User();
            $user->firstname = '';
            $user->lastname = '';
            $user->email = $this->getInvitation()->email;
            $user->password = '';
            $user->email_verified_at = Carbon::now();
            $user->api_token = Str::random(60);
            $user->is_active = false;
            $user->save();
        }
        return $user;
    }

    public function fulfill()
    {
        $user = $this->findOrCreate();
        $organisation = $this->getInvitation()->organisation;

        if($user->isMemberOf($organisation)){
            return false;
        }

        $organisation->users()->attach($user->id, ['user_role' => $this->getInvitation()->user_role_id]);
        // $this->getInvitation()->delete();
        return true;
    }
}
