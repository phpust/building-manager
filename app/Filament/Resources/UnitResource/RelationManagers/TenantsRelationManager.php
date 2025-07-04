<?php

namespace App\Filament\Resources\UnitResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\User;

class TenantsRelationManager extends RelationManager
{
    protected static string $relationship = 'tenants';
    protected static ?string $title       = 'مستأجران';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('user_id')
                ->label('مستأجر')
                ->options(User::pluck('name', 'id'))
                ->searchable()
                ->required(),

            Forms\Components\DatePicker::make('from_date')
                ->label('از تاریخ')
                ->required()->jalali(),

            Forms\Components\DatePicker::make('to_date')
                ->label('تا تاریخ')
                ->nullable()->jalali(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('مستأجر'),
                Tables\Columns\TextColumn::make('from_date')->label('از')->jalaliDate(),
                Tables\Columns\TextColumn::make('to_date')->label('تا')->jalaliDate()->placeholder('اکنون'),
            ])
            ->headerActions([ Tables\Actions\CreateAction::make() ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
