<?php

namespace App\Filament\Resources\UnitExpenseDetailResource\Pages;

use App\Filament\Resources\UnitExpenseDetailResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUnitExpenseDetails extends ListRecords
{
    protected static string $resource = UnitExpenseDetailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
