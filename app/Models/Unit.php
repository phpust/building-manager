<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Morilog\Jalali\Jalalian;

class Unit extends Model
{
    protected $fillable = ['number', 'floor', 'description'];

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
    ];

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

    // relation between user table and owner fields
    public function ownerUser()
    {
        return $this->belongsTo(User::class, 'current_owner_id');
    }
    
    public function owners()
    {
        return $this->hasMany(UnitOwner::class);
    }

    public function lastOwner()
    {
        return $this->hasOne(UnitOwner::class)
            ->whereNull('to_date')
            ->latestOfMany();
    }

    // base on current active financial_year
    public function ownersInFinancialYear()
    {
        $fy   = Setting::financialYear();
        $start = (new Jalalian($fy, 1, 1))->toCarbon();
        $end   = (new Jalalian($fy + 1, 1, 1))->toCarbon();

        return $this->owners()
            ->whereDate('from_date', '<=', $end)
            ->where(function ($q) use ($start) {
                $q->whereNull('to_date')
                  ->orWhereDate('to_date', '>=', $start);
            })
            ->orderBy('from_date')
            ;
    }

    public function getOwnersNameInFinancialYearAttribute(): string
    {
        return $this->ownersInFinancialYear()
            ->get()
            ->map(fn($owner) => $owner->user->name)
            ->filter()
            ->join(' - ');
    }

    public function tenantUser()
    {
        return $this->belongsTo(User::class, 'current_tenant_id');
    }
    
    public function tenants()   { 
        return $this->hasMany(UnitTenant::class); 
    }

    public function lastTenant()
    {
        return $this->hasOne(UnitTenant::class)
            ->whereNull('to_date')
            ->latestOfMany();
    }

    public function tenantsInFinancialYear()
    {
        $fy   = Setting::financialYear();
        $start = (new Jalalian($fy, 1, 1))->toCarbon();
        $end   = (new Jalalian($fy + 1, 1, 1))->toCarbon();

        return $this->tenants()
            ->whereDate('from_date', '<=', $end)
            ->where(function ($q) use ($start) {
                $q->whereNull('to_date')
                  ->orWhereDate('to_date', '>=', $start);
            })
            ->orderBy('from_date');
    }

    public function getTenantsNameInFinancialYearAttribute(): string
    {
        return $this->tenantsInFinancialYear()
            ->get()
            ->map(fn($tenant) => $tenant->user->name)
            ->filter()
            ->join(' - ');
    }

    public function eligibleUsersForFinancialYear(int $financialYear)
    {
        $owners = $this->ownersInFinancialYear()
            ->with('user')
            ->get()
            ->pluck('user.name', 'user.id');

        $tenants = $this->tenantsInFinancialYear()
            ->with('user')
            ->get()
            ->mapWithKeys(fn($tenant) => [
                $tenant->user->id => $tenant->user->name . ' (مستأجر)'
            ]);

        return $owners
        ->union($tenants) 
        ->sortBy(null, SORT_NATURAL | SORT_FLAG_CASE);
    }
}

