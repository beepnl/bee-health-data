<?php

namespace App\Http\Requests;

use App\Models\RegistrationInvitation;
use App\Models\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class RegistrationInvitationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [];

        if($this->method() === 'PUT' && $this->route('registration_invitation') instanceof RegistrationInvitation){
            if($this->request->has('resend')){
                return $rules;
            }
            $rules = [
                'user_role_id' => 'required|in:'. UserRole::get()->pluck('id')->join(',')
            ];
        }

        return $rules;
    }
}
