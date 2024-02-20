<?php

namespace App\Rules;

use App\Models\RegistrationInvitation;
use App\Models\User;
use Illuminate\Contracts\Validation\Rule;

class NotExistsMember implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $user = User::where('email', $value)->first();
        $invitation = RegistrationInvitation::where('email', $value)->first();
        if($invitation){
            return false;
        }
        if(!$user){
            return true;
        }
        if(!$user->isMember()){
            return true;
        }

        return false;

    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Тhis email already exists as a member.';
    }
}
