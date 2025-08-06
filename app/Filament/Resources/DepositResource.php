<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DepositResource\Pages;
use App\Filament\Resources\DepositResource\RelationManagers;
use App\Models\Deposit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\RawJs;
use App\Models\Setting;

class DepositResource extends Resource
{
    protected static ?string $model = Deposit::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';


    public static function getModelLabel(): string
    {
        return 'ودیعه پرداختی';
    }

    public static function getPluralModelLabel(): string
    {
        return 'ودیعه های پرداختی';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('عنوان ودیعه')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Hidden::make('financial_year')->default(fn () => Setting::financialYear()),
                Forms\Components\Select::make('unit_id')
                    ->label('واحد')
                    ->options(function () {
                        return \App\Models\Unit::all()->mapWithKeys(fn($unit) => [
                            (string)$unit->id => 'واحد ' . $unit->number
                        ])->toArray();
                    })
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\Select::make('payer_type')
                    ->label('پرداخت کننده')
                    ->options([
                        'owner' => 'مالک',
                        'tenant' => 'مستاجر',
                    ])
                    ->required(),

                Forms\Components\TextInput::make('amount')
                    ->label('مبلغ ودیعه')
                    ->mask(RawJs::make('$money($input)'))
                    ->stripCharacters([','])
                    ->required(),

                Forms\Components\Toggle::make('is_paid')
                    ->label('وضعیت پرداخت')
                    ->default(false)->inline(false),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('شناسه')->sortable(),
                Tables\Columns\TextColumn::make('financial_year')->label('سال مالی')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('unit.owners_name_in_financial_year')->label('مالک واحد')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('payer_type')->label('پرداخت کننده')->sortable(),
                Tables\Columns\TextColumn::make('amount')->label('مبلغ ودیعه')->sortable()->money('IRR', true, 'fa'),
                Tables\Columns\IconColumn::make('is_paid')->label('وضعیت پرداخت')->boolean(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListDeposits::route('/'),
            'create' => Pages\CreateDeposit::route('/create'),
            'edit' => Pages\EditDeposit::route('/{record}/edit'),
        ];
    }
}
