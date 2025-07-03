<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UnitResource\Pages;
use App\Filament\Resources\UnitResource\RelationManagers;
use App\Models\Unit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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
        return 'واحد ها';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('number')
                    ->label('شماره واحد')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('owner_name')
                    ->label('نام مالک')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('tenant_name')
                    ->label('نام مستاجر')
                    ->nullable()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('شناسه')->sortable(),
                Tables\Columns\TextColumn::make('number')->label('شماره واحد')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('owner_name')->label('نام مالک')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('tenant_name')->label('نام مستاجر')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('created_at')->label('تاریخ ایجاد')->jalaliDate(),
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
            RelationManagers\UnitExpenseDetailsRelationManager::class,
            RelationManagers\DepositsRelationManager::class,
        ];
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUnits::route('/'),
            'create' => Pages\CreateUnit::route('/create'),
            'edit' => Pages\EditUnit::route('/{record}/edit'),
        ];
    }
}
