<?php

namespace App\Filament\Resources\UnitResource\Pages;

use App\Filament\Resources\UnitResource;
use Filament\Actions;
use Filament\Resources\Pages\Page;
use App\Models\Unit;
use App\Models\Setting;
use Carbon\Carbon;


class UnitFinancialReport extends Page
{
    protected static string $resource = UnitResource::class;

    protected static string $view = 'filament.resources.unit-resource.pages.unit-financial-report';

    public Unit $record;  
    public string $filter = 'all';
    public ?int $tenantId = null;

    public function mount(Unit $record): void
    {
        $this->record = $record;
    }
    
    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('بازگشت به واحدها')
                ->url(static::getResource()::getUrl()),
        ];
    }

    public function hasTenantInCurrentYear(): bool
    {
        $fy = Setting::financialYear(); 

        $start = Carbon::createFromFormat('Y', $fy)->startOfYear();
        $end = Carbon::createFromFormat('Y', $fy)->endOfYear();

        return $this->record->tenantsInFinancialYear()
            ->exists();
    }
}
