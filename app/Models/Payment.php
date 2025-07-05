<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasFinancialYear;

class Payment extends Model
{
    use HasFinancialYear;

    protected $fillable = ['unit_id', 'user_id', 'payer_type', 'amount', 'paid_at', 'financial_year', 'description'];

    protected $casts = [
        'paid_at' => 'date',
        'amount' => 'decimal:0',
        'type' => 'string',
    ];

    public const TYPE_NORMAL     = 'normal';
    public const TYPE_CARRY_OVER = 'carry_over';
    public const TYPE_REFUND     = 'refund';


    public function scopeRefuns($q) { 
        return $q->where('type', self::TYPE_REFUND); 
    }

    public function scopeCarryOvers($q) { 
        return $q->where('type', self::TYPE_CARRY_OVER); 
    }

    public function scopeNormals($q)    { 
        return $q->where('type', self::TYPE_NORMAL);  
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function usages(): HasMany
    {
        return $this->hasMany(PaymentUsage::class);
    }

    // dummy column to calculate amount used in payment usage table
    public function getAmountUsedAttribute()
    {
        return 0;
    }

    public function getUsedAmountForPayable($payableType, $payableId)
    {
        return $this->usages()
            ->where('payable_type', $payableType)
            ->where('payable_id', $payableId)
            ->sum('amount_used');
    }

    public function getRemainingAmountAttribute()
    {
        $used = $this->usages()->sum('amount_used');
        return $this->amount - $used;
    }

    public function unitExpenseDetails()
    {
        return $this->hasMany(UnitExpenseDetail::class, 'unit_id', 'unit_id');
    }

    public function deposits()
    {
        return $this->hasMany(Deposit::class, 'unit_id', 'unit_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }


}
