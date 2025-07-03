<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Traits\HasFinancialYear;

class UnitExpenseDetail extends Model
{
    use HasFinancialYear;
    
    protected $fillable = ['expense_id', 'unit_id', 'payer_type', 'amount_due', 'is_paid', 'financial_year'];

    protected $casts = [
        'amount_due'=> 'decimal:0',
        'is_paid'   => 'boolean',
    ];

    protected static function booted()
    {
        static::deleting(function (UnitExpenseDetail $detail) {
            $detail->paymentUsages()->delete();
        });

        static::updated(function (UnitExpenseDetail $detail) {
            $detail->enforcePaymentLimit();
            $detail->checkIfNowItISPaidOrNot();
        });
    }

    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function paymentUsages(): MorphMany
    {
        return $this->morphMany(PaymentUsage::class, 'payable');
    }

    public function payments()
    {
        return $this->hasManyThrough(
            Payment::class,
            PaymentUsage::class,
            'payable_id',       // Foreign key on PaymentUsage table...
            'id',               // Foreign key on Payment table...
            'id',               // Local key on UnitExpenseDetail table...
            'payment_id'        // Local key on PaymentUsage table...
        )->where('payment_usages.payable_type', self::class);
    }

    public function enforcePaymentLimit()
    {
        $totalPaid = $this->paymentUsages()->sum('amount_used');

        if ($totalPaid <= $this->amount_due) {
            return;
        }

        $excess = $totalPaid - $this->amount_due;

        $usages = $this->paymentUsages()->orderByDesc('id')->get();

        foreach ($usages as $usage) {
            if ($excess <= 0) break;

            if ($usage->amount_used > $excess) {
                $usage->update(['amount_used' => $usage->amount_used - $excess]);
                break;
            } else {
                $excess -= $usage->amount_used;
                $usage->delete();
            }
        }
    }

    public function checkIfNowItIsPaidOrNot()
    {
        $totalPaid = $this->paymentUsages()->sum('amount_used');

        $isNowPaid = $totalPaid >= $this->amount_due;

        if ($this->is_paid !== $isNowPaid) {
            $this->updateQuietly(['is_paid' => $isNowPaid]); 
        }
    }
}
