<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Filament\Facades\Filament;
use App\Models\Unit;

class UnitReportController extends Controller
{
    public function showReport(Request $request)
    {
        Filament::setCurrentPanel(Filament::getDefaultPanel());

        $unitId       = $request->get('unit_id');
        $selectedUnit = $unitId ? Unit::find($unitId) : null;

        $units = Unit::orderBy('floor')
                     ->orderBy('number')
                     ->get();

        return view('guest.unit-report', compact('units', 'selectedUnit'));
    }
}
