<?php

namespace App\Modules\Process\Requests;

use App\Core;
use App\Modules\Process\Rules\ProcessExists;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class EditProcess extends FormRequest
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
        Core::addBreadcrumb(__('Proses Listesi'), route('processes.index'), 'fa-square');

        Core::failedValidation($validator, __('Proses Düzenle'));
    }
}
