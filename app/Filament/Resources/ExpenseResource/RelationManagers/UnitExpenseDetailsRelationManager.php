<?php

namespace App\Filament\Resources\ExpenseResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\UnitExpenseDetail;
use Filament\Tables\Actions\Action;
use Filament\Support\RawJs;
use Morilog\Jalali\Jalalian;

class UnitExpenseDetailsRelationManager extends RelationManager
{
    protected static string $relationship = 'unitExpenseDetails';

    protected static ?string $title = 'هزینه های مرتبط با  این هزینه';

    public static function getPluralModelLabel(): string
    {
        return 'جزئیات هزینه ';
    }
    
    protected static function getModelLabel(): ?string
    {
        return 'جزئیات هزینه ';
    }

    public function getRelationshipQuery(): Builder
    {
        return UnitExpenseDetail::query()
            ->where('unit_id', $this->ownerRecord->unit_id);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('شناسه')->sortable(),
                Tables\Columns\TextColumn::make('expense.financial_year')->label('سال مالی')->sortable(),
                Tables\Columns\TextColumn::make('unit.owner_name')->label('مالک'),
                Tables\Columns\TextColumn::make('expense.title')->label('عنوان'),
                Tables\Columns\TextColumn::make('payer_type')->label('بر عهده'),
                Tables\Columns\TextColumn::make('amount_due')->label('مبلغ بدهی')->formatStateUsing(fn($state) => number_format($state) . ' ریال'),

                Tables\Columns\IconColumn::make('is_paid')->label('وضعیت پرداخت')->boolean(),

                Tables\Columns\TextColumn::make('total_paid')
                    ->label('مبلغ پرداخت شده')
                    ->getStateUsing(function ($record) {
                        $amount = number_format($record->paymentUsages->sum('amount_used'));
                        return fa_numbers($amount) . ' ریال';
                    }),

            ])
            ->filters([
                Tables\Filters\Filter::make('linked_to_payment')
                    ->label('مرتبط با این پرداخت')
                    ->query(function (Builder $query) {
                        return $query->whereHas('paymentUsages', function ($q) {
                            $q->where('payment_id', $this->ownerRecord->id);
                        });
                    }),
                Tables\Filters\SelectFilter::make('financial_year')
                    ->label('سال مالی')
                    ->options(function () {
                        $currentYear = Jalalian::now()->getYear();
                        $years = [];
                        for ($i = $currentYear - 10; $i <= $currentYear + 1; $i++) {
                            $years[$i] = $i;
                        }
                        return $years;
                    }),
            ])

            ->headerActions([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
