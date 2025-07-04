<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UnitResource\Pages;
use App\Filament\Resources\UnitResource\RelationManagers;
use App\Models\Unit;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UnitResource extends Resource
{
    protected static ?string $model = Unit::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    public static function getModelLabel(): string
    {
        return 'واحد';
    }

    public static function getPluralModelLabel(): string
    {
        return 'واحدها';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('number')
                    ->label('شماره واحد')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('floor')
                    ->label('طبقه')
                    ->numeric()
                    ->nullable(),

                Forms\Components\Textarea::make('description')
                    ->label('توضیحات')
                    ->rows(3)
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('شناسه')->sortable(),
                Tables\Columns\TextColumn::make('number')->label('شماره واحد')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('floor')->label('طبقه')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('ownersNameInFinancialYear')->label('مالک ')->searchable(),
                Tables\Columns\TextColumn::make('tenantsNameInFinancialYear')->label('مستأجر ')->searchable(),
                Tables\Columns\TextColumn::make('created_at')->label('تاریخ ایجاد')->jalaliDate(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\Action::make('financial')
                    ->label('وضعیت مالی')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->url(fn (Unit $record) => Pages\UnitFinancialReport::getUrl([$record]))
                    ->openUrlInNewTab(),
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
            RelationManagers\UnitExpenseDetailsRelationManager::class,
            RelationManagers\DepositsRelationManager::class,
            RelationManagers\OwnersRelationManager::class,
            RelationManagers\TenantsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'      => Pages\ListUnits::route('/'),
            'create'     => Pages\CreateUnit::route('/create'),
            'edit'       => Pages\EditUnit::route('/{record}/edit'),
            'financial'  => Pages\UnitFinancialReport::route('/{record}/financial'),
        ];
    }
}
