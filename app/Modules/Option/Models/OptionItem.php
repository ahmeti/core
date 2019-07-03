<?php

namespace App\Modules\Option\Models;

use App\Modules\Core\Scopes\CompanyScope;
use App\Modules\Core\Traits\CompanyTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class OptionItem extends Model
{
    use CompanyTrait;
    use SoftDeletes;

    protected $table = 'ai_option_items';
    protected $guarded = ['id'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->company_id = self::companyId();
            $model->id = OptionItem::withTrashed()->max('id') + 1;

            $model->created_id = self::userId();
            $model->updated_id = self::userId();
        });
        static::updating(function ($model) {
            $model->updated_id = self::userId();
        });
    }

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
