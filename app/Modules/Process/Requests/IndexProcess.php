<?php

namespace App\Modules\Process\Requests;

use App\Core;
use Illuminate\Foundation\Http\FormRequest;

class IndexProcess extends FormRequest
{
    protected function prepareForValidation()
    {
        if ( is_null($this->input('orderByCol')) ){
            $this->merge([
                'orderByCol' => 'id'
            ]);
        }

        if ( is_null($this->input('orderByType')) ){
            $this->merge([
                'orderByType' => 'desc'
            ]);
        }
    }

    public function authorize()
    {
        return Core::checkPermission(3);
    }

    public function rules()
    {
        return [];
    }

    public function messages()
    {
        return [];
    }
}
