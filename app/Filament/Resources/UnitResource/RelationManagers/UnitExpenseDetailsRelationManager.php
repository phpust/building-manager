<?php

namespace App\Filament\Resources\UnitResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Support\RawJs;

class UnitExpenseDetailsRelationManager extends RelationManager
{
    protected static string $relationship = 'unitExpenseDetails';

    protected static ?string $recordTitleAttribute = 'id';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return 'جزئیات هزینه واحدها';
    }

    public static function getPluralModelLabel(): string
    {
        return 'جزئیات هزینه واحدها';
    }
    
    protected static function getModelLabel(): ?string
    {
        return 'جزئیات هزینه واحد';
    }


    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('expense_id')
                ->relationship('expense', 'title')
                ->label('انتخاب هزینه')
                ->required(),
            Forms\Components\Select::make('payer_type')
                ->label('پرداخت کننده')
                ->options([
                    'owner' => 'مالک',
                    'tenant' => 'مستأجر',
                ])
                ->required(),
            Forms\Components\TextInput::make('amount_due')
                ->label('سهم  از هزینه')
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
            Tables\Columns\TextColumn::make('id')->label('شناسه'),
            Tables\Columns\TextColumn::make('expense.title')->label('عنوان هزینه'),
            Tables\Columns\TextColumn::make('payer_type')->label('پرداخت کننده'),
            Tables\Columns\TextColumn::make('amount_due')
                ->label('سهم  از هزینه')->money('IRR', true, 'fa'),
            Tables\Columns\TextColumn::make('total_paid')
                ->label('مبلغ پرداخت شده')
                ->getStateUsing(function ($record) {
                    $amount = number_format($record->paymentUsages->sum('amount_used'));
                    return fa_numbers($amount) . ' ریال';
                }),

            Tables\Columns\IconColumn::make('is_paid')->label('وضعیت پرداخت')->boolean(),
        ])
        ->filters([
            //
        ])
        ->headerActions([
            Tables\Actions\CreateAction::make()->label('ایجاد یک هزینه جدید برای این واحد'),
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
