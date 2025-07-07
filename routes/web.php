<?php

use Illuminate\Support\Facades\Route;
use App\Helpers\FinancialYear;
use App\Http\Controllers\UnitReportController;

Route::get('/', [UnitReportController::class, 'showReport'])->name('unit.report');

Route::get('/set-financial-year/{year}', function ($year) {
    FinancialYear::set($year);
    return redirect()->back();
})->name('set.financial.year');
