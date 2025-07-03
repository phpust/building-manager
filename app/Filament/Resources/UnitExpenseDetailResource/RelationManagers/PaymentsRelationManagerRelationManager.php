<?php

namespace App\Filament\Resources\UnitExpenseDetailResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PaymentsRelationManagerRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('شناسه')->sortable(),
                Tables\Columns\TextColumn::make('financial_year')->label('سال مالی')->sortable(),
                Tables\Columns\TextColumn::make('unit.owner_name')->label('مالک'),
                Tables\Columns\TextColumn::make('payer_type')->label('پرداخت شده توسط'),
                Tables\Columns\TextColumn::make('amount')->label('مبلغ  کل پرداختی')->formatStateUsing(fn($state) => number_format($state) . ' ریال'),
                Tables\Columns\TextColumn::make('amount_used')
                    ->label('میزان پرداخت شده برای این هزینه')
                    ->formatStateUsing(function ($state, $record) {
                        $unitExpenseDetail = $this->ownerRecord;
                        return number_format($record->getUsedAmountForPayable(
                            get_class($unitExpenseDetail),
                            $unitExpenseDetail->id
                        )) . ' ریال';
                    })
                    ->sortable(false),


            ])
            ->paginated()
            ->defaultSort('created_at', 'desc');
    }
}
