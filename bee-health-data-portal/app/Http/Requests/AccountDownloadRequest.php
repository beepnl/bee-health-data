<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class AccountDownloadRequest extends FormRequest
{
    private $user;
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (is_null($this->getUser())) {
            return false;
        }

        if (!hash_equals((string) $this->route('hash'), sha1($this->getUser()->email))) {
            return false;
        }

        return true;
    }

    public function getUser()
    {
        return $this->user ?: $this->user = User::where('id', $this->route('id'))->active()->first();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }
}
