<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use App\Helpers\FinancialYear;
use App\Models\Setting;

trait HasFinancialYear
{
    protected static function bootHasFinancialYear(): void
    {
        static::addGlobalScope('financial_year', fn ($q) =>
            $q->where($q->getModel()->getTable().'.financial_year', Setting::financialYear())
        );


        static::creating(function ($model) {
            $model->financial_year = Setting::financialYear();
        });
    }

    public function scopeAllYears(Builder $q): Builder
    {
        return $q->withoutGlobalScope('financial_year');
    }

    public function scopeForYear(Builder $q, int $year): Builder
    {
        return $q->allYears()->where($q->getModel()->getTable().'.financial_year', $year);
    }
}
