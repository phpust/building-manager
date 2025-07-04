<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'is_active'];

    protected static function booted(): void
    {
        static::saved(function (Setting $setting) {
            if ($setting->key === 'financial_year' && $setting->is_active) {
                DB::transaction(function () use ($setting) {
                    self::where('key', 'financial_year')
                        ->where('id', '!=', $setting->id)
                        ->update(['is_active' => false]);
                });

                cache()->forget('financial_year');
            }
        });
    }

    public static function financialYear(): int
    {
        return cache()->rememberForever('financial_year', function () {
            return (int) self::query()
                ->where('key', 'financial_year')
                ->where('is_active', true)
                ->value('value')
                ?: now()->year;
        });
    }

    public static function setFinancialYear(int $year): void
    {
        self::updateOrCreate(['key' => 'financial_year'], ['value' => $year]);
        cache()->forget('financial_year');
    }

    public function makeActive(): void
    {
        DB::transaction(function () {
            self::where('key', 'financial_year')
                ->where('id', '!=', $this->id)
                ->update(['is_active' => false]);

            $this->update(['is_active' => true]);
        });
        cache()->forget('financial_year');

    }
}
