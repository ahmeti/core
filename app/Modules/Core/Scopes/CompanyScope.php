<?php

namespace App\Modules\Core\Scopes;

use App\Core;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class CompanyScope implements Scope
{
    private $columnName = 'company_id';

    public function __construct($columnName = null)
    {
        if( ! empty($columnName) ){
            $this->columnName = $columnName;
        }
    }

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $builder->where($this->columnName, Core::companyId());
    }
}