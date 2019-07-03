<?php

namespace App\Modules\User\Models;


use App\Modules\Core\Scopes\CompanyScope;
use App\Modules\Core\Traits\CompanyTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Permission extends Model
{
    use CompanyTrait;

    protected $table = 'ai_permissions';
    public $incrementing = false;
    public $timestamps = false;
    protected $guarded = ['company_id'];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new CompanyScope);
        static::creating(function ($model) {
            $model->company_id = self::companyId();
        });
    }

    public function getDB()
    {
        $db = DB::table($this->table)->where('company_id', self::companyId());

        return $db;
    }
}
