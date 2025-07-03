<?php

namespace App\Filament\Resources\UnitResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Support\RawJs;
use Morilog\Jalali\Jalalian;

class DepositsRelationManager extends RelationManager
{
    protected static string $relationship = 'deposits';

    protected static ?string $recordTitleAttribute = 'id';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return 'جزئیات  ودیعه های پرداختی'; // عنوان دلخواه به فارسی
    }

    public static function getPluralModelLabel(): string
    {
        return 'ودیعه های پرداختی';
    }
    
    protected static function getModelLabel(): ?string
    {
        return 'ودیعه  پرداختی';
    }


    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('financial_year')
                ->label('سال مالی')
                ->options(function () {
                    $currentYear = Jalalian::now()->getYear();
                    $years = [];
                    for ($i = $currentYear - 10; $i <= $currentYear + 1; $i++) {
                        $years[$i] = $i;
                    }
                    return $years;
                })
                ->default(Jalalian::now()->getYear())
                ->required(),
            Forms\Components\Select::make('payer_type')
                ->label('پرداخت کننده')
                ->options([
                    'owner' => 'مالک',
                    'tenant' => 'مستأجر',
                ])
                ->required(),
            Forms\Components\TextInput::make('amount')
                ->label('مبلغ')
                ->mask(RawJs::make('$money($input)'))
                ->stripCharacters([','])
                ->required(),
                Forms\Components\Toggle::make('is_paid')
                    ->label('وضعیت پرداخت')
                    ->default(false)->inline(false),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('financial_year')->label('سال مالی'),
            Tables\Columns\TextColumn::make('payer_type')->label('Payer'),
            Tables\Columns\TextColumn::make('amount')->money('IRR', true, 'fa'),
            Tables\Columns\IconColumn::make('is_paid')->label('وضعیت پرداخت')->boolean(),
        ])
        ->filters([
            //
        ])
        ->headerActions([
            Tables\Actions\CreateAction::make()->label('ایجاد سند ودیعه جدید برای این واحد'),
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\DeleteBulkAction::make(),
        ]);
    }
}
