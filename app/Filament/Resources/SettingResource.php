<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettingResource\Pages;
use App\Filament\Resources\SettingResource\RelationManagers;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\ToggleColumn;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';


    public static function getModelLabel(): string
    {
        return 'تنظیمات';
    }

    public static function getPluralModelLabel(): string
    {
        return 'تنظیمات';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('key')
                    ->default('financial_year')
                    ->dehydrated(),
                Forms\Components\TextInput::make('value')
                    ->label('سال مالی')
                    ->numeric()
                    ->required()
                    ->rules(['digits:4', 'min:1396', 'max:1500']),
                Forms\Components\Toggle::make('is_active')
                    ->label('فعال؟')
                    ->onColor('success')
                    ->inline(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('value')
                    ->label('سال')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('فعال')
                    ->boolean(),
                ToggleColumn::make('is_active')
                    ->label('فعال؟')
                    ->onColor('success')
                    ->offColor('secondary')
                    ->afterStateUpdated(function (Setting $record, bool $state, $livewire) {
                        if($state){
                            $record->makeActive();
                            $record->refresh();
                            $livewire->dispatch('financial-year-updated');
                        }
                    }),
                Tables\Columns\TextColumn::make('updated_at')->label('آخرین بروزرسانی')->jalaliDate()->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('فقط سال فعال')
                    ->trueLabel('فعال')
                    ->falseLabel('غیرفعال'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSettings::route('/'),
            'create' => Pages\CreateSetting::route('/create'),
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }
}
