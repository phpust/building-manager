<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use App\Services\FinancialYearCloser;
use App\Models\Setting;

class CloseFinancialYear extends Page
{
    protected static ?string $navigationGroup = 'پایان سال';
    protected static ?string $navigationIcon  = 'heroicon-o-arrow-path';
    protected static string  $view            = 'filament.pages.close-financial-year';
    protected static ?string $title           = 'بستن سال مالی';


    public int $fromYear;
    public int $toYear;

    public function mount(): void
    {
        $this->fromYear = Setting::financialYear();
        $this->toYear   = $this->fromYear + 1;
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Placeholder::make('fromYear')
                ->label('سال مالی جاری (در حال بسته شدن)')
                ->content($this->fromYear),

            Forms\Components\TextInput::make('toYear')
                ->label('سال مالی جدید')
                ->numeric()
                ->minValue($this->fromYear + 1)
                ->required(),

            Forms\Components\Toggle::make('confirm')
                ->label('تمام موارد بالا را تأیید می کنم')
                ->required(),
        ]);
    }

    public function close(): void
    {
        $this->validate(['toYear' => 'required|numeric|min:' . ($this->fromYear + 1)]);

        resolve(FinancialYearCloser::class)
            ->close($this->fromYear, $this->toYear);

        Notification::make()
            ->title("سال مالی {$this->fromYear} با موفقیت بسته شد و {$this->toYear} فعال گردید.")
            ->success()
            ->send();

        $this->redirect(static::getUrl());
    }

}
