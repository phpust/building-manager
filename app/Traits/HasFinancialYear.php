<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use App\Helpers\FinancialYear;

trait HasFinancialYear
{
    protected static function bootHasFinancialYear()
    {
        static::addGlobalScope('financial_year', function (Builder $builder) {
            $builder->where('financial_year', FinancialYear::get());
        });

        static::creating(function ($model) {
            if (empty($model->financial_year)) {
                $model->financial_year = FinancialYear::get();
            }
        });
    }
}
