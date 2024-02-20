<?php

namespace App\Http\Requests;

use App\Models\Dataset;
use App\Models\Organisation;
use Illuminate\Foundation\Http\FormRequest;

class AuthorizationRequestRequest extends FormRequest
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
        if($this->method() === 'POST'){
            return [
                'requesting_dataset_id' => 'required|exists:'. Dataset::class . ',id',
                'requesting_organisation_id' => 'exists:'. Organisation::class . ',id'
            ];
        }

        if($this->method() === 'PUT') {
            return [
                'is_approved' => 'required|boolean',
                'response_note' => 'max:500'
            ];
        }

        return [];
    }
}
