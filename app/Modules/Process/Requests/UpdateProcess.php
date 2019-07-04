<?php

namespace App\Modules\Process\Requests;

use App\Core;
use App\Modules\Process\Rules\ProcessExists;
use App\Modules\Status\Rules\StatusExists;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class UpdateProcess extends FormRequest
{
    protected function prepareForValidation()
    {
        $this->merge([
            'id' => $this->route('process')
        ]);
    }

    public function authorize()
    {
        return Core::checkPermission(5);
    }

    public function rules()
    {
        return [
            'id' => ['required', new ProcessExists],

            'name' => ['required', 'string', 'min:1', 'max:100'],
            'status' => ['required', new StatusExists('process_status', 'Durum bilgisine ulaşılmadı.')],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        Core::failedValidation($validator);
    }
}
