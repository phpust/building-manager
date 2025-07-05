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
        if (! is_array($this->unit_ids) || empty($this->unit_ids)) {
            return;
        }

        $unitIds   = $this->unit_ids;
        $count     = count($unitIds);

        $base = intdiv($this->total_amount, $count);

        $remainder = $this->total_amount - ($base * $count);

        foreach ($unitIds as $index => $unitId) {

            $share = $base + ($index < $remainder ? 1 : 0);

            UnitExpenseDetail::create([
                'expense_id'     => $this->id,
                'unit_id'        => $unitId,
                'payer_type'     => $this->payer_type,
                'amount_due'     => $share,
                'is_paid'        => false,
                'financial_year' => $this->financial_year,
            ]);
        }
    }

    public function createOrUpdateUnitExpenseDetails()
    {
        if (!is_array($this->unit_ids) || empty($this->unit_ids)) {
            return;
        }

        $unitIds = $this->unit_ids;
        $count = count($unitIds);
        $baseAmount = intdiv($this->total_amount, $count);
        $remainder = $this->total_amount - ($baseAmount * $count);

        $amountsPerUnit = [];
        foreach ($unitIds as $index => $unitId) {
            $amountsPerUnit[$unitId] = $baseAmount + ($index < $remainder ? 1 : 0);
        }

        $existingUnitIds = $this->unitExpenseDetails()->pluck('unit_id')->toArray();

        $newUnitIds = array_diff($unitIds, $existingUnitIds);
        $removedUnitIds = array_diff($existingUnitIds, $unitIds);
        $commonUnitIds = array_intersect($unitIds, $existingUnitIds);

        foreach ($newUnitIds as $unitId) {
            UnitExpenseDetail::create([
                'expense_id'     => $this->id,
                'unit_id'        => $unitId,
                'payer_type'     => $this->payer_type ?? 'owner',
                'amount_due'     => $amountsPerUnit[$unitId],
                'is_paid'        => false,
                'financial_year' => $this->financial_year,
            ]);
        }

        foreach ($this->unitExpenseDetails as $detail) {
            if (in_array($detail->unit_id, $commonUnitIds)) {
                $detail->update(['amount_due' => $amountsPerUnit[$detail->unit_id]]);
            }
        }

        if (!empty($removedUnitIds)) {
            $this->unitExpenseDetails()
                ->whereIn('unit_id', $removedUnitIds)
                ->update(['amount_due' => 0]);
        }
    }

    
}

