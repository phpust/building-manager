<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class FinancialYear
{
    public static function get(): string
    {
        $value = DB::table('settings')->where('key', 'financial_year')->value('value');
        return $value ?? '1404';
    }

    public static function set(string $year): void
    {
        session(['financial_year' => $year]);
    }
}
