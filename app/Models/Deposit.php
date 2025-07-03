<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Traits\HasFinancialYear;

class Deposit extends Model
{
    use HasFinancialYear;
    
    protected $fillable = ['unit_id', 'payer_type', 'amount', 'financial_year', 'is_paid'];

    protected $casts = [
        'amount' => 'decimal:0',
        'is_paid' => 'boolean',
    ];


    protected static function booted()
    {
        static::deleting(function (Deposit $deposit) {
            $deposit->paymentUsages()->delete();
        });
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function paymentUsages(): MorphMany
    {
        return $this->morphMany(PaymentUsage::class, 'payable');
    }
}
