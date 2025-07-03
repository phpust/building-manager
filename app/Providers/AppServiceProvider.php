<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Forms\Components\DateTimePicker;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Table::$defaultDateDisplayFormat = 'Y/m/d';
        Table::$defaultDateTimeDisplayFormat = 'Y/m/d H:i:s';

        Infolist::$defaultDateDisplayFormat = 'Y/m/d';
        Infolist::$defaultDateTimeDisplayFormat = 'Y/m/d H:i:s';

        DateTimePicker::$defaultDateDisplayFormat = 'Y/m/d';
        DateTimePicker::$defaultDateTimeDisplayFormat = 'Y/m/d H:i';
        DateTimePicker::$defaultDateTimeWithSecondsDisplayFormat = 'Y/m/d H:i:s';
        app()->setLocale('fa');
    }
}
