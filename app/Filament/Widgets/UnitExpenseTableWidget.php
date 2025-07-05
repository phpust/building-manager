<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use App\Models\Unit;


class UnitExpenseTableWidget extends Widget
{
    protected static string $view = 'filament.widgets.unit-expense-table-widget';
    protected int | string | array $columnSpan = 'full';         

    public $unit;

    public function mount(): void
    {
        $this->unit = auth()->user()->ownedUnits()->first()
            ?? auth()->user()->rentedUnits()->first();

        $this->hasUnit = $this->unit !== null;
    }

    protected function getViewData(): array
    {
        return [
            'unit' => $this->unit,
            'hasUnit' => $this->hasUnit,
        ];
    }
}
