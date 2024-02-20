<?php

namespace App\Http\Requests;

use App\Models\Dataset;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class AuthorizationRequest extends FormRequest
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
            'dataset_id' => "required|exists:" . Dataset::class . ",id",
            'organisation_id' => "required_without:user_id|exists:" . Organisation::class . ",id",
            'user_id' => "required_without:organisation_id|exists:" . User::class . ",id",
        ];
    }
}
