<?php

namespace App\Http\Requests;

use App\Models\Dataset;
use App\Models\FileVersion;
use Illuminate\Foundation\Http\FormRequest;

class FileRequest extends FormRequest
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
            // 'file' => 'file',
            // 'id' => 'exists:'.FileVersion::class.',id',
            'dataset_id' => "exists:" . Dataset::class . ",id",
            'description' => 'max:140',
            'filename' => 'max:140',
        ];
    }
}
