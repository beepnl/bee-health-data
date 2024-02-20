<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AccountActivateRequest extends FormRequest
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
        return [
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'password' => 'required|regex:/^(?=.*[A-Z])(?=.*[!@#$&*])(?=.*[0-9]).{8,}$/|confirmed',
            'accepted_terms_and_conditions' => 'required',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'password.regex' => 'The password format is invalid. Please use (One lowercase character, One uppercase character, One number, One special character and 8 characters minimum)',
        ];
    }
}

