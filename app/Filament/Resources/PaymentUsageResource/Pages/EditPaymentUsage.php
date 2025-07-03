<?php

namespace App\Filament\Resources\PaymentUsageResource\Pages;

use App\Filament\Resources\PaymentUsageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPaymentUsage extends EditRecord
{
    protected static string $resource = PaymentUsageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
