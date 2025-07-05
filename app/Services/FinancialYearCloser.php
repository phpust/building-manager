<?php

namespace App\Services;

use App\Models\{Setting, Unit, Expense, Deposit, UnitExpenseDetail};
use Illuminate\Support\Facades\DB;
use App\Models\Payment;
use App\Models\YearlyBalance;
use App\Models\BuildingYearlyTotal;

class FinancialYearCloser
{
    public function close(int $fromYear, int $toYear): void
    {
        DB::transaction(function () use ($fromYear, $toYear) {

            // reset buildingYearlyTotal record 
            $buildingYearlyTotal = BuildingYearlyTotal::updateOrCreate(
                ['financial_year' => $fromYear],
                [
                    'total_ending_balance'     => 0,
                    'total_deposits_paid'      => 0,
                    'total_deposits_remaining' => 0,
                ]);

            Unit::chunk(200, function ($units) use ($fromYear, $toYear) {

                foreach ($units as $unit) {

                    $expenses = UnitExpenseDetail::forYear($fromYear)
                        ->where('unit_id', $unit->id)
                        ->with('expense')
                        ->get(); 

                    $charge   = $expenses->filter(fn($e) => $e->expense->is_charge ?? false);
                    $other    = $expenses->reject(fn($e) => $e->expense->is_charge ?? false);

                    $totalChargePaid   = $charge->where('is_paid', true)->sum('amount_due');
                    $totalOtherExpenses = $other->sum('amount_due');
                
                    $depositRecords = Deposit::forYear($fromYear)->where('unit_id', $unit->id)->get();
                    $paymentRecords = Payment::forYear($fromYear)->where('unit_id', $unit->id)->latest()->get();

                    $depositsPaid   = $depositRecords->sum(fn($d) => $d->paymentUsages->sum('amount_used'));
                    $chargePaid     = $charge->where('is_paid', true)->sum('amount_due');

                    /////////////////////////////////////////////////   Unit   /////////////////////////////////////////////////////
                    $lastYearBalance = YearlyBalance::where('unit_id', $unit->id)
                                           ->where('financial_year', $fromYear)
                                           ->first();

                    $openingBalance        = $lastYearBalance?->ending_balance   ?? 0;
                    $openingDepositsPaid   = $lastYearBalance?->ending_deposits_paid  ?? 0;
                    $openingDepositsRemain = $lastYearBalance?->ending_deposits_remaining  ?? 0;


                    $totalOpening = YearlyBalance::where('financial_year', $fromYear)
                        ->selectRaw('SUM(ending_balance)   as balance, ' .
                                    'SUM(ending_deposits_paid)      as dep_paid, ' .
                                    'SUM(ending_deposits_remaining) as dep_rem')
                        ->first();

                    $data = [
                            'chargeRecords'         => $charge->sortBy(fn($e) => $e->expense->date_from),
                            'otherExpenseRecords'   => $other->sortBy(fn($e) => $e->expense->date_from),

                            'totalPayments'                 => $paymentRecords->sum('amount'),
                            'totalDepositsPaid'             => $depositsPaid,
                            'totalChargeRequired'           => $charge->sum('amount_due'),
                            'totalChargePaid'               => $chargePaid,
                            'totalExpense'                  => $other->sum('amount_due') - ($openingBalance < 0 ? abs($openingBalance) : 0),
                            'totalDeposits'                 => $depositRecords->sum('amount'),


                            'totalOpeningBalance'        => $totalOpening->balance   ?? 0,
                            'totalOpeningDepositsPaid'   => $totalOpening->dep_paid  ?? 0,
                            'totalOpeningDepositsRemain' => $totalOpening->dep_rem   ?? 0,
                        ];

                    $totalExpenseWithPreviousYear = $other->sum('amount_due') - ($openingBalance < 0 ? abs($openingBalance) : 0);
                    $totalPaymentsWithoutDeposit  = $paymentRecords->sum('amount') - $depositsPaid;
                    $totalChargeRemaining = $charge->where('is_paid', false)->sum(fn ($e) => $e->amount_due - $e->paymentUsages->sum('amount_used'));
                    $totalDepositsRemaining = $depositRecords->sum('amount') - $depositsPaid;

                    if ($openingBalance < 0) {
                        $totalExpenseWithPreviousYear += abs($openingBalance);
                    }

                    $netBalance = $totalPaymentsWithoutDeposit - $totalExpenseWithPreviousYear;

                    $amountDue = max(-$netBalance, $totalChargeRemaining);
                    $otherDue  = $netBalance < 0 ? abs($netBalance) : 0;
                    
                    if ($totalChargeRemaining > $otherDue) {
                        $otherDue = 0;
                    }else{
                        $totalChargeRemaining = 0;
                    }
                    
                    if ($totalChargeRemaining > $otherDue) {
                        $otherDue = 0;
                    }else{
                        $totalChargeRemaining = 0;
                    }

                    $creditInFund = max($netBalance - $totalChargeRemaining, 0);

                    $debtDue      = $totalChargeRemaining + $otherDue;
                    $totalFinalDue = $debtDue + $totalDepositsRemaining;

                    // if balanceDifference > 0 then set starting_balance to this value, but if it is negative then
                    // put 0 in it and create new dept for this unit called last year dets. but in last year save
                    // it in negative form 

                    YearlyBalance::updateOrCreate(
                        ['unit_id' => $unit->id, 'financial_year' => $fromYear],
                        [
                            'ending_balance'  => $netBalance,
                            'ending_deposits_paid' => $depositsPaid + $openingDepositsPaid,
                            'ending_deposits_remaining' => $totalDepositsRemaining,
                        ]
                    );

                    // dd($netBalance , $amountDue, $debtDue, $totalChargeRemaining, $depositsPaid,  $openingDepositsPaid, $totalDepositsRemaining);

                    // if netBalance is negative then set it 0 as starting balance and generate a dept bill
                    $starting_balance = max($netBalance, 0); 

                    YearlyBalance::updateOrCreate(
                        ['unit_id' => $unit->id, 'financial_year' => $toYear],
                        [
                            'starting_balance'  => $starting_balance,
                            'starting_deposits_paid' => $depositsPaid + $openingDepositsPaid,
                            'starting_deposits_remaining' => $totalDepositsRemaining,
                        ]
                    );

                    if ($netBalance < 0) {
                        $carryOverExpense = Expense::withoutEvents(function() use ($fromYear, $toYear, $netBalance, $unit) {
                            return Expense::firstOrCreate(
                                [
                                    'title'          => "بدهی انتقالی سال {$fromYear}",
                                    'financial_year' => $toYear,
                                    'is_charge'      => false,
                                ],
                                [
                                    'total_amount'   => abs($netBalance),
                                    'shares_count'   => 1,
                                    'date_from'      => now(),
                                    'payer_type'     => 'owner',
                                    'unit_ids'       => [$unit->id],  
                                ]
                            );
                        });
                        
                        $carryOverExpense->updateQuietly(['financial_year' => $toYear]);

                        $detail = UnitExpenseDetail::create([
                            'unit_id'      => $unit->id,
                            'expense_id'   => $carryOverExpense->id,
                            'payer_type'   => 'owner',
                            'amount_due'   => abs($netBalance),
                            'is_paid'      => false,
                            'financial_year'  => $toYear,
                        ]);
                        $detail->updateQuietly(['financial_year' => $toYear]);
                    }

                    if ($totalDepositsRemaining > 0) {
                        $deposit = Deposit::create([
                            'unit_id'         => $unit->id,
                            'amount'          => $totalDepositsRemaining,
                            'title'           => 'باقی‌مانده ودیعه سال  های قبل',
                            'financial_year'  => $toYear,
                            'is_paid'         => false,
                        ]);
                        
                        $deposit->updateQuietly(['financial_year' => $toYear]);
                    }

                    if ($netBalance > 0) {
                        $virtualPayment = Payment::create([
                            'unit_id'        => $unit->id,
                            'amount'         => $netBalance,
                            'payer_type'     => 'owner',
                            'description'    => "انتقال مانده از سال {$fromYear}",
                            'paid_at'        => now(),
                            'financial_year' => $toYear,
                            'type'           => Payment::TYPE_CARRY_OVER,
                        ]);
                        
                        $virtualPayment->updateQuietly(['financial_year' => $toYear]);
                    }

                    BuildingYearlyTotal::where('financial_year', $fromYear)
                        ->update([
                            'total_ending_balance'     => DB::raw('total_ending_balance + '     . $netBalance),
                            'total_deposits_paid'      => DB::raw('total_deposits_paid + '      . $depositsPaid),
                            'total_deposits_remaining' => DB::raw('total_deposits_remaining + ' . $totalDepositsRemaining),
                        ]);
                }
            });

            $setting = Setting::updateOrCreate(
                ['key' => 'financial_year', 'value' => $toYear],
                ['is_active' => true]
            );
            $setting->makeActive(); 
        });
    }

}
