<?php
namespace App\Modules\Process\Models;

use App\Modules\Core\Scopes\CompanyScope;
use App\Modules\Core\Traits\CompanyTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Process extends Model
{
    use CompanyTrait;
    use SoftDeletes;

    protected $table = 'ai_processes';
    public $incrementing = false;
    protected $guarded = ['id'];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new CompanyScope);
        static::creating(function ($model) {
            $model->company_id = self::companyId();
            $model->id = Process::withTrashed()->max('id') + 1;

            $model->created_id = self::userId();
            $model->updated_id = self::userId();
        });
        static::updating(function ($model) {
            $model->updated_id = self::userId();
        });
    }

    public static function getName($id)
    {
        $model = Process::select('name')->find($id);
        return $model ? $model->name : null;
    }

    public function getDB($softDelete = true)
    {
        $db = DB::table($this->table)->where('company_id', self::companyId());;

        if($softDelete){
            $db->whereNull('deleted_at');
        }

        return $db;
    }
}
