<?php

namespace App\Http\Requests;

use App\Models\Organisation;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\NotExistsMember;
class MembershipRequest extends FormRequest
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
        
        // Update
        if($this->method() === 'PUT'){
            return [
                'user_role_id' => 'required|exists:' . UserRole::class . ',id',
                'organisation_id' => 'required|exists:' . Organisation::class . ',id',
            ];
        }
        // Store
        if($this->method() === 'POST'){
            return [
                'email' => ['email', 'required', new NotExistsMember],
                'user_role_id' => 'required|exists:' . UserRole::class . ',id',
                'organisation_id' => 'required|exists:' . Organisation::class . ',id',
            ];
        }

        // Destroy
        if ($this->method() === 'DELETE') {
            return [
                'user_role_id' => 'required|exists:' . UserRole::class . ',id',
                'organisation_id' => 'required|exists:' . Organisation::class . ',id',
            ];
        }

        return $rules;
    }
}
