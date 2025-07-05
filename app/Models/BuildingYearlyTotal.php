<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BuildingYearlyTotal extends Model
{
    protected $fillable = [
        'financial_year',
        'total_deposits_paid',
        'total_deposits_remaining',
        'total_ending_balance',
    ];
}