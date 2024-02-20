<?php

namespace App\Http\Requests;

use App\Models\Dataset;
use Illuminate\Foundation\Http\FormRequest;

class AuthorRequest extends FormRequest
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
        $rules = [
            'lastname' => 'required|max:25',
            'initials' => 'required|max:25',
            'organisation' => 'max:25',
            'dataset_id' => "exists:" . Dataset::class . ",id"
        ];

        if($this->method() === 'DELETE'){
            $rules = array_diff($rules, array_diff_key($rules, ['dataset_id' => 0]));
        }
        if ($this->method() === 'PUT') {
            $rules = array_diff($rules, array_diff_key($rules, ['dataset_id' => 0])) + ['order' => "min:1"];
        }

        return $rules;
    }
}
