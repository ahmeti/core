<?php

namespace App\Modules\Process\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchProcess extends FormRequest
{
    public function authorize()
    {
        return true;
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
