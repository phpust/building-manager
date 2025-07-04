<?php

namespace App\Filament\Resources\ExpenseResource\Pages;

use App\Filament\Resources\ExpenseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExpense extends EditRecord
{
    protected static string $resource = ExpenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('create_expense')
                ->label('ثبت هزینه جدید')
                ->icon('heroicon-o-plus')
                ->url(fn () => ExpenseResource::getUrl('create'))
                ->color('success'),

            Actions\DeleteAction::make(),
        ];
    }
}
