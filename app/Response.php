<?php

namespace App;

use Illuminate\Support\Facades\Facade;

class Response extends Facade {
    protected static function getFacadeAccessor() { return 'responseservice'; }
}