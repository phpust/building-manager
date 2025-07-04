<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasFinancialYear;

class PaymentUsage extends Model
{
    use HasFinancialYear;
    
    protected $fillable = ['payment_id', 'payable_type', 'payable_id', 'amount_used'];

    protected $casts = [
        'amount_used' => 'decimal:0',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }
}

