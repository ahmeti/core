<?php

namespace App\Modules\Page\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Page extends Model
{
    use SoftDeletes;

    protected $table = 'ai_pages';
    public $timestamps = false;
    protected $guarded = ['id'];

    public static function getName($id)
    {
        $model = Page::select('name')->find($id);
        return $model ? $model->name : null;
    }

    public function getDB()
    {
        $db = DB::table($this->table);

        return $db;
    }
}
