<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasFinancialYear;

class Expense extends Model
{
    use HasFinancialYear;
    
    protected $fillable = ['title', 'is_charge', 'total_amount', 'payer_type', 'date_from', 'financial_year', 'unit_ids', 'attachment'];

    protected $casts = [
        'date_from' => 'date',
        'unit_ids' => 'array',
        'total_amount' => 'decimal:0',
    ];

    public function unitExpenseDetails(): HasMany
    {
        return $this->hasMany(UnitExpenseDetail::class);
    }

    public function getSharesCountAttribute(): int
    {
        return $this->unitExpenseDetails()->count();
    }

    protected static function booted()
    {
        static::created(function (Expense $expense) {
            $expense->createUnitExpenseDetails();
        });

        static::updated(function (Expense $expense) {
            $expense->createOrUpdateUnitExpenseDetails();
        });
    }

    public function createUnitExpenseDetails()
    {
        if (!is_array($this->unit_ids)) {
            return;
        }

        $amountPerUnit = $this->calculateUnitShare();

        foreach ($this->unit_ids as $unitId) {
            UnitExpenseDetail::create([
                'expense_id'     => $this->id,
                'unit_id'        => $unitId,
                'payer_type'     => $this->payer_type,
                'amount_due'     => $amountPerUnit,
                'is_paid'        => false,
                'financial_year' => $this->financial_year,
            ]);
        }
    }

    public function createOrUpdateUnitExpenseDetails()
    {
        if (!is_array($this->unit_ids)) {
            return;
        }

        $existingUnitIds = $this->unitExpenseDetails()->pluck('unit_id')->toArray();
        $newUnitIds = array_diff($this->unit_ids, $existingUnitIds);

        $amountPerUnit = $this->calculateUnitShare();

        // add new persons
        foreach ($newUnitIds as $unitId) {
            UnitExpenseDetail::create([
                'expense_id'     => $this->id,
                'unit_id'        => $unitId,
                'payer_type'     => $this->payer_type,
                'amount_due'     => $amountPerUnit,
                'is_paid'        => false,
                'financial_year' => $this->financial_year,
            ]);
        }

        // set removed units amount to 0
        $removedUnitIds = array_diff($existingUnitIds, $this->unit_ids);
        if (!empty($removedUnitIds)) {
            $this->unitExpenseDetails()->whereIn('unit_id', $removedUnitIds)->update(['amount_due' => 0]);
        }

        // update amount_due value in pre existing unitExpenseDetail
        foreach ($this->unitExpenseDetails as $detail) {
            $detail->update(['amount_due' => $amountPerUnit]);
        }
    }

    public function calculateUnitShare(): float
    {
        $count = count($this->unit_ids);
        return $count > 0 ? $this->total_amount / $count : 0;
    }

    
}

