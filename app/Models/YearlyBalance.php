<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class YearlyBalance extends Model
{
    protected $fillable = [
        'unit_id',
        'financial_year',
        'starting_balance',
        'starting_deposits_paid',
        'starting_deposits_remaining',
        'ending_balance',
        'ending_deposits_paid',
        'ending_deposits_remaining',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}