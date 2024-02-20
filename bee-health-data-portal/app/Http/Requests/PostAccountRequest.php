<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostAccountRequest extends FormRequest
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
        if(
            empty($this->request->get('old_password'))
            && empty($this->request->get('password'))
            && empty($this->request->get('password_confirmation')) 
        ){
            return [
                'firstname' => 'required|string',
                'lastname' => 'required|string',
            ];
        }
        return [
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'old_password' => 'required|password',
            'password' => 'required|regex:/^(?=.*[A-Z])(?=.*[!@#$&*])(?=.*[0-9]).{8,}$/|confirmed',
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
