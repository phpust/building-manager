<?php

namespace App\Filament\Resources\PaymentResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Support\RawJs;
use App\Models\Deposit;
use App\Models\PaymentUsage;
use Illuminate\Database\Eloquent\Builder;
use Morilog\Jalali\Jalalian;

class DepositRelationManager extends RelationManager
{
    protected static string $relationship = 'deposits';

    protected static ?string $title = 'ودیعه ها';

    public static function getPluralModelLabel(): string
    {
        return 'ودیعه های پرداختی';
    }
    
    protected static function getModelLabel(): ?string
    {
        return 'ودیعه  پرداختی';
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('شناسه')->sortable(),
                Tables\Columns\TextColumn::make('financial_year')->label('سال مالی')->sortable(),
                Tables\Columns\TextColumn::make('unit.owner_name')->label('مالک'),
                Tables\Columns\TextColumn::make('payer_type')->label('بر عهده'),
                Tables\Columns\TextColumn::make('amount')
                    ->label('مبلغ ودیعه')
                    ->money('IRR', true),
                Tables\Columns\IconColumn::make('is_paid')
                    ->label('وضعیت پرداخت')
                    ->boolean(),
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
            ->actions([
                Action::make('editDepositPayment')
                    ->label('پرداخت')
                    ->form(function (Deposit $record) {
                        return [
                            Forms\Components\Placeholder::make('remaining_payment')
                                ->label('مبلغ باقی‌مانده از پرداختی')
                                ->content(function (callable $get, callable $set, $livewire) {
                                    $record = $livewire->ownerRecord;
                                    return number_format($record->remaining_amount) . ' ریال';
                                }),
                            Forms\Components\Placeholder::make('remaining_deposit')
                                ->label('مبلغ باقیمانده ودیعه')
                                ->content(function (Deposit $deposit, callable $get, callable $set, $livewire) {
                                    $payment = $livewire->ownerRecord;
                                    $remaining = number_format($deposit->amount - $deposit->paymentUsages->sum('amount_used'));
                                    return $remaining . ' ریال';
                                }),
                            Forms\Components\TextInput::make('amount_used')
                                ->label('مبلغ پرداخت‌شده')
                                ->mask(RawJs::make('$money($input)'))
                                ->stripCharacters([','])
                                ->required(),
                        ];
                    })
                    ->mountUsing(function (Deposit $record, Forms\ComponentContainer $form, $livewire) {
                        $payment = $livewire->ownerRecord;

                        $usage = PaymentUsage::where('payment_id', $payment->id)
                            ->where('payable_type', Deposit::class)
                            ->where('payable_id', $record->id)
                            ->first();

                        if ($usage) {
                            $form->fill([
                                'amount_used' => $usage->amount_used,
                            ]);
                        }
                    })
                    ->action(function (array $data, Deposit $record, $livewire) {
                        $payment = $this->ownerRecord;

                        $usage = $record->paymentUsages()
                            ->where('payment_id', $payment->id)
                            ->first();

                        if (!$usage) {
                            $usage = PaymentUsage::create([
                                'payment_id'    => $payment->id,
                                'payable_type'  => Deposit::class,
                                'payable_id'    => $record->id,
                                'amount_used'   => 0,
                            ]);
                        }

                        $otherUsages = $payment->usages()
                            ->where('id', '!=', $usage->id)
                            ->sum('amount_used');

                        $remainingFromPayment = $payment->amount - $otherUsages;

                        $newAmount = $data['amount_used'];

                        if ($newAmount > $remainingFromPayment) {
                            \Filament\Notifications\Notification::make()
                                ->title('مبلغ واردشده بیشتر از مانده پرداخت است')
                                ->danger()
                                ->send();
                            return;
                        }

                        $otherUsagesForDeposit = $record->paymentUsages()
                            ->where('id', '!=', $usage->id)
                            ->sum('amount_used');

                        $depositRemaining = $record->amount - $otherUsagesForDeposit;

                        if ($newAmount > $depositRemaining) {
                            $newAmount = $depositRemaining;
                            \Filament\Notifications\Notification::make()
                                ->title('مبلغ ودیعه کمتر از این مقدار بود و فقط به اندازه آن هزینه لحاظ شد.')
                                ->danger()
                                ->send();
                        }

                        $usage->update([
                            'amount_used' => $newAmount,
                        ]);

                        $totalPaid = $record->paymentUsages()->sum('amount_used');
                        $record->is_paid = $totalPaid >= $record->amount;
                        $record->save();

                        \Filament\Notifications\Notification::make()
                            ->title('پرداخت با موفقیت ویرایش شد')
                            ->success()
                            ->send();
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
            ]);
    }
}
