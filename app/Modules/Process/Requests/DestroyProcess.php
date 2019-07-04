<?php

namespace App\Modules\Process\Requests;

use App\Core;
use App\Modules\Process\Rules\ProcessExists;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class DestroyProcess extends FormRequest
{
    protected function prepareForValidation()
    {
        $this->merge([
            'id' => $this->route('process')
        ]);
    }

    public function authorize()
    {
        return Core::checkPermission(6);
    }

    public function rules()
    {
        return [
            'id' => ['required', new ProcessExists],
        ];
    }

    public function messages()
    {
        return [
            'id.required' => __('Proses bilgisine ulaşılamadı.'),
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        Core::addBreadcrumb(__('Process List'), route('processes.index'), 'fa-square');

        Core::failedValidation($validator, __('Delete Process'));
    }
}
