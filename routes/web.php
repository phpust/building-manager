<?php

use Illuminate\Support\Facades\Route;
use App\Helpers\FinancialYear;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/set-financial-year/{year}', function ($year) {
    FinancialYear::set($year);
    return redirect()->back();
})->name('set.financial.year');
