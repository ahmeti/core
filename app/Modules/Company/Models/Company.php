<?php

namespace App\Modules\Company\Models;

use App\Core;
use App\Modules\Core\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Company extends Model
{
    use SoftDeletes;

    protected $table = 'ai_companies';
    public $incrementing = false;
    protected $guarded = ['id'];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new CompanyScope('id'));
        static::creating(function ($model) {
            $model->id = Company::withTrashed()->max('id') + 1;

            $model->created_id = Core::userId();
            $model->updated_id = Core::userId();
        });
        static::updating(function ($model) {
            $model->updated_id = Core::userId();
        });
    }

    public static function getName($id)
    {
        $model = Company::select('name')->find($id);
        return $model ? $model->name : null;
    }


    public function getDB($softDelete = true)
    {
        $db = DB::table($this->table)->where('id', Core::companyId());

        if($softDelete){
            $db->whereNull('deleted_at');
        }
        return $db;
    }
}
