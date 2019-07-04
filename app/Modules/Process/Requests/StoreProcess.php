<?php

namespace App\Modules\Process\Requests;

use App\Core;
use App\Modules\Status\Rules\StatusExists;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class StoreProcess extends FormRequest
{
    public function authorize()
    {
        return Core::checkPermission(4);
    }

    public function rules()
    {
        return [
            'name' => ['required', 'string', 'min:1', 'max:100'],
            'status' => ['required', new StatusExists('process_status', 'Durum bilgisine ulaşılmadı.')],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        Core::failedValidation($validator);
    }
}
