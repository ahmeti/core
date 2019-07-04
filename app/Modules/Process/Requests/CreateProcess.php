<?php

namespace App\Modules\Process\Requests;

use App\Core;
use Illuminate\Foundation\Http\FormRequest;

class CreateProcess extends FormRequest
{
    protected function prepareForValidation()
    {
        $this->merge([
            'status' => 1
        ]);
    }

    public function authorize()
    {
        return Core::checkPermission(4);
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
