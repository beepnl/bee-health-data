<?php

namespace App\Http\Requests;

use App\Models\Dataset;
use App\Models\Keyword;
use App\Models\Organisation;
use App\Models\License;;
use App\Rules\DoiRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\VarDumper\Cloner\Data;

class DatasetRequest extends FormRequest
{
    // private $authors;

    public function __construct()
    {
    }
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if($this->publication_state == Dataset::PUBLICATION_STATES_DRAFT){
            return [];
        }

        return [
            'name' => 'required|max:140',
            'organisation_id' => 'required|exists:'. Organisation::class .',id',
            'description' => 'required|max:500',
            'digital_object_identifier' => [new DoiRule],
            'license' => 'required|exists:'. License::class .',id',
            'access_type' => 'required|in:'. Dataset::ACCESS_TYPE_REGISTERED_USERS . ',' . Dataset::ACCESS_TYPE_BY_REQUEST . ',' . Dataset::ACCESS_TYPE_OWNING_ORGANISATION_ONLY . ',' . Dataset::ACCESS_TYPE_BGOOD_PARTNERS . ',' . Dataset::ACCESS_TYPE_OPEN_ACCESS,
            'agreement' => 'required',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        if ($this->publication_state == Dataset::PUBLICATION_STATES_DRAFT) {
            return;
        }
        $validator->after(function ($validator) {
            if (!$this->authorsIsValid()) {
                $validator->errors()->add('authors', 'Мust have at least one author!');
            }
            if (!$this->keywordsIsValid()) {
                $validator->errors()->add('keywords', 'Мust have at least one keyword!');
            }
            if(!$this->filesIsValid()){
                $validator->errors()->add('files', 'Мust have at least one file!');
            }
            if($this->access_type === Dataset::ACCESS_TYPE_BY_REQUEST && !$this->authorizationOrganisationsValid()){
                $validator->errors()->add('authorization_organisations', 'Мust have at least one authorization organisations!');
            }
        });
    }

    private function authorsIsValid()
    {
        return $this->route('dataset')->authors()->exists();
    }
    private function keywordsIsValid()
    {
        return $this->route('dataset')->keywords()->exists();
    }
    private function filesIsValid(){
        return $this->route('dataset')->files()->exists();
    }
    private function authorizationOrganisationsValid(){
        return $this->route('dataset')->authorization_organisations()->exists();
    }
}
