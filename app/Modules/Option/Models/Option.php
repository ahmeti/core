<?php

namespace App\Modules\Option\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Option extends Model
{
    use SoftDeletes;

    protected $table = 'ai_options';
    public $timestamps = false;
    protected $guarded = ['id'];

    public static function getName($id)
    {
        $model = Option::select('name')->find($id);
        return $model ? $model->name : null;
    }

    public function getDB()
    {
        $db = DB::table($this->table);

        return $db;
    }
}
