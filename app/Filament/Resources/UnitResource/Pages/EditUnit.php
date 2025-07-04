<?php

namespace App\Filament\Resources\UnitResource\Pages;

use App\Filament\Resources\UnitResource;
use Filament\Actions;
use App\Models\Unit;
use Filament\Resources\Pages\EditRecord;

class EditUnit extends EditRecord
{
    protected static string $resource = UnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\Action::make('financial')
            ->label('گزارشات مالی')
            ->url(fn (Unit $record) => UnitFinancialReport::getUrl([$record]))
        ];
    }
}
