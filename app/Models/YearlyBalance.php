<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class YearlyBalance extends Model
{
    protected $fillable = [
        'unit_id',
        'financial_year',
        'starting_balance',
        'starting_deposits',
        'ending_balance',
        'ending_deposits',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}