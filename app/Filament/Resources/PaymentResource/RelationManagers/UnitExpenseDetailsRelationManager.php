<?php

namespace App\Filament\Resources\PaymentResource\RelationManagers;

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

    protected static ?string $title = 'هزینه های مرتبط با واحد';

    public static function getPluralModelLabel(): string
    {
        return 'جزئیات هزینه واحدها';
    }
    
    protected static function getModelLabel(): ?string
    {
        return 'جزئیات هزینه واحد';
    }

    public function getRelationshipQuery(): Builder
    {
        return UnitExpenseDetail::query()
            ->where('unit_id', $this->ownerRecord->unit_id);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('هزینههای واحد')
                    ->required()
                    ->maxLength(255),
            ]);
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
                Tables\Columns\TextColumn::make('paid_from_this_payment')
                ->label('پرداخت شده از این پرداخت')
                ->getStateUsing(function ($record) {
                    $amount = $record->paymentUsages()
                        ->where('payment_id', $this->ownerRecord->id)
                        ->sum('amount_used');

                    return fa_numbers(number_format($amount)) . ' ریال';
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
                Action::make('editPaymentUsage')
                    ->label('پرداخت')
                    ->form(function (UnitExpenseDetail $record) {
                        return [
                            Forms\Components\Placeholder::make('remaining_amount')
                                ->label('مبلغ باقی مانده از پرداختی')
                                ->content(function (callable $get, callable $set, $livewire) {
                                    $record = $livewire->ownerRecord;
                                    return number_format($record->remaining_amount) . ' ریال';
                                }),
                            Forms\Components\Placeholder::make('remaining_amounts')
                                ->label('مبالغ باقی‌مانده از بدهی')
                                ->content(function (UnitExpenseDetail $expense, callable $get, callable $set, $livewire) {
                                    $payment = $livewire->ownerRecord;
                                    
                                    $paymentRemaining = number_format($payment->remaining_amount) . ' ریال';

                                    $expenseRemaining = number_format(($expense->amount_due - $expense->paymentUsages->sum('amount_used'))) . ' ریال';

                                    return $expenseRemaining;
                                }),
                            Forms\Components\TextInput::make('amount_used')
                                ->label('مبلغ پرداخت‌شده')
                                ->mask(RawJs::make('$money($input)'))
                                ->stripCharacters([','])
                                ->required(),
                        ];
                    })
                    ->mountUsing(function (UnitExpenseDetail $record, Forms\ComponentContainer $form, $livewire) {
                        $payment = $livewire->ownerRecord;

                        $paymentUsage = \App\Models\PaymentUsage::where('payment_id', $payment->id)
                            ->where('payable_type', \App\Models\UnitExpenseDetail::class)
                            ->where('payable_id', $record->id)
                            ->first();

                        if ($paymentUsage) {
                            $form->fill([
                                'amount_used' => $paymentUsage->amount_used,
                            ]);
                        }
                    })
                    ->action(function (array $data, UnitExpenseDetail $record, $livewire) {
                        $payment = $this->ownerRecord;

                        $paymentUsage = $record->paymentUsages()
                            ->where('payment_id', $payment->id)
                            ->first();

                        if (!$paymentUsage) {
                            \App\Models\PaymentUsage::create([
                                'payment_id'    => $payment->id,
                                'payable_type'  => \App\Models\UnitExpenseDetail::class,
                                'payable_id'    => $record->id,
                                'amount_used'   => 0,
                            ]);

                            $paymentUsage = $record->paymentUsages()
                                ->where('payment_id', $payment->id)
                                ->first();
                        }
                        
                        $previousAmountUsed = $paymentUsage->amount_used;


                        // check to not exceed maximum payment remaining
                        $totalOtherUsages = $payment->usages()->where('id', '!=', $paymentUsage->id)->sum('amount_used');
                        $remainingFromPayment = $payment->amount - $totalOtherUsages;
                        $newAmount = $data['amount_used'];

                        if ($newAmount > $remainingFromPayment) {
                            \Filament\Notifications\Notification::make()
                                ->title('مبلغ واردشده بیشتر از مانده پرداخت است')
                                ->danger()
                                ->send();
                            return;
                        }

                        // check to not exceed maximum dept
                        $totalOtherUsages = $record->paymentUsages()
                            ->where('id', '!=', $paymentUsage->id)
                            ->sum('amount_used');

                        $debtRemaining = $record->amount_due - $totalOtherUsages;

                        if ($newAmount > $debtRemaining) {
                            $newAmount = $debtRemaining;
                            \Filament\Notifications\Notification::make()
                                ->title('مبلغ بدهی کمتر از این مقدار بود و فقط به اندازه بدهی هزینه لحاظ شد.')
                                ->danger()
                                ->send();
                        }

                        $paymentUsage->update([
                            'amount_used' => $newAmount,
                        ]);

                        // به‌روزرسانی وضعیت پرداخت
                        $totalPaid = $record->paymentUsages()->sum('amount_used');
                        $record->is_paid = $totalPaid >= $record->amount_due;
                        $record->save();

                        \Filament\Notifications\Notification::make()
                            ->title('ویرایش با موفقیت انجام شد')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([]);
    }
}
