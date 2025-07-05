<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UnitExpenseDetailResource\Pages;
use App\Filament\Resources\UnitExpenseDetailResource\RelationManagers;
use App\Models\UnitExpenseDetail;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\RawJs;
use Morilog\Jalali\Jalalian;

class UnitExpenseDetailResource extends Resource
{
    protected static ?string $model = UnitExpenseDetail::class;

    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';
    
    protected static bool $shouldRegisterNavigation = false;


    public static function getModelLabel(): string
    {
        return 'جزئیات بدهی واحد';
    }

    public static function getPluralModelLabel(): string
    {
        return 'جزئیات بدهی های واحد';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('financial_year')->default(fn () => Setting::financialYear()),
                Forms\Components\Select::make('expense_id')
                    ->relationship('expense', 'title')
                    ->label('هزینه')
                    ->required(),

                Forms\Components\Select::make('unit_id')
                    ->relationship('unit', 'owner_name')
                    ->label('واحد')
                    ->required(),

                Forms\Components\Select::make('payer_type')
                    ->options([
                        'owner' => 'مالک',
                        'tenant' => 'مستاجر',
                    ])
                    ->label('پرداخت کننده')
                    ->required(),

                Forms\Components\TextInput::make('amount_due')
                    ->label('مبلغ بدهی')
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
                Tables\Columns\TextColumn::make('expense.title')->label('عنوان هزینه')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('unit.owner_name')->label('مالک واحد')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('payer_type')->label('پرداخت کننده')->sortable(),
                Tables\Columns\TextColumn::make('amount_due')->label('مبلغ بدهی')->sortable()->money('IRR', true, 'fa'),
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
            RelationManagers\PaymentsRelationManagerRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUnitExpenseDetails::route('/'),
            'create' => Pages\CreateUnitExpenseDetail::route('/create'),
            'edit' => Pages\EditUnitExpenseDetail::route('/{record}/edit'),
        ];
    }
}
