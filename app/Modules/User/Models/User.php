<?php

namespace App\Modules\User\Models;

use App\Modules\Core\Scopes\CompanyScope;
use App\Modules\Core\Traits\CompanyTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    use CompanyTrait;

    protected $table = 'ai_users';
    public $incrementing = false;
    protected $guarded = ['id'];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        if( self::companyId() > 0 ){
            static::addGlobalScope(new CompanyScope);
        }

        static::creating(function ($model) {
            $model->company_id = self::companyId();
            $model->id = User::withTrashed()->max('id') + 1;

            $model->created_id = self::userId();
            $model->updated_id = self::userId();
        });
        static::updating(function ($model) {
            $model->updated_id = self::userId();
        });
    }
}
