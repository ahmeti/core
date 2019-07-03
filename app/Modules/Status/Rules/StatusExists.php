<?php

namespace App\Modules\Status\Rules;

use App\Core;
use Illuminate\Contracts\Validation\Rule;

class StatusExists implements Rule
{
    private $_key;
    private $_message;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($key, $message)
    {
        $this->_key = $key;
        $this->_message = $message;
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
        $enums = array_keys(Core::enumsValue($this->_key));

        if( in_array($value, $enums) ){
            return true;
        }

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        // return trans('validation.exists');
        return $this->_message;
    }
}
