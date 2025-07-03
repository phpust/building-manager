<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    protected $fillable = ['number', 'owner_name', 'tenant_name'];

    public function unitExpenseDetails(): HasMany
    {
        return $this->hasMany(UnitExpenseDetail::class);
    }

    public function deposits(): HasMany
    {
        return $this->hasMany(Deposit::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}

