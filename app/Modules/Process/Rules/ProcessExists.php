<?php

namespace App\Modules\Process\Rules;

use App\Modules\Process\Models\Process;
use Illuminate\Contracts\Validation\Rule;

class ProcessExists implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return Process::find((int)$value) ? true : false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('Process bilgisine ulaşılamadı.');
    }
}
