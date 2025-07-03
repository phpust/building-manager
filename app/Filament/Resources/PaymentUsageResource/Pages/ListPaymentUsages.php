<?php

namespace App\Filament\Resources\PaymentUsageResource\Pages;

use App\Filament\Resources\PaymentUsageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPaymentUsages extends ListRecords
{
    protected static string $resource = PaymentUsageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
