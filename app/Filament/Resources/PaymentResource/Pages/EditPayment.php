<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPayment extends EditRecord
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('create_expense')
                ->label('ثبت  پرداختی جدید')
                ->icon('heroicon-o-plus')
                ->url(fn () => PaymentResource::getUrl('create'))
                ->color('success'),

            Actions\DeleteAction::make(),
        ];
    }
}
