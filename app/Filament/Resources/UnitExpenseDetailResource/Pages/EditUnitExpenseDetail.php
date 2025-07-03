<?php

namespace App\Filament\Resources\UnitExpenseDetailResource\Pages;

use App\Filament\Resources\UnitExpenseDetailResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUnitExpenseDetail extends EditRecord
{
    protected static string $resource = UnitExpenseDetailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
