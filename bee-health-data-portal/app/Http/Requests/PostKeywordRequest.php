<?php

namespace App\Http\Requests;

use App\Models\Keyword;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class PostKeywordRequest extends FormRequest
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
            "name.*"  => "required|string|distinct|min:1|max:25",
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'name.*.required' => 'The keyword field is required',
            'name.*.min' => 'The keyword must be at least :min characters.',
        ];
    }
}
