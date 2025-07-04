<?php

namespace App\Services;

use App\Models\{Setting, Unit, Expense, Deposit, UnitExpenseDetail};
use Illuminate\Support\Facades\DB;
use App\Models\Payment;
use App\Models\YearlyBalance;

class FinancialYearCloser
{
    public function close(int $fromYear, int $toYear): void
    {
        DB::transaction(function () use ($fromYear, $toYear) {
            Unit::chunk(200, function ($units) use ($fromYear, $toYear) {

                foreach ($units as $unit) {

                    $allExpenses    = UnitExpenseDetail::forYear($fromYear)->where('unit_id', $unit->id)->with('expense')->get();        
                    $otherExpenses  = $allExpenses->reject(fn ($e) => $e->expense->is_charge ?? false)->sortBy(fn ($e) => $e->expense->date_from);
                    $totalExpense = $otherExpenses->sum('amount_due');
                    
                    $chargeRecords  = $allExpenses->filter(fn ($e) => $e->expense->is_charge ?? false)->sortBy(fn ($e) => $e->expense->date_from);
                    $totalChargePaid   = $chargeRecords->where('is_paid', true)->sum('amount_due');
                
                    $depositRecords = Deposit::forYear($fromYear)->where('unit_id', $unit->id)->get();
                    $totalDepositsPaid = $depositRecords->sum(fn($d) => $d->paymentUsages->sum('amount_used'));
                    $totalDepositsRemaining = $depositRecords->sum('amount') - $totalDepositsPaid;

                    // base on totalDepositsPaid set ending_deposits and if totalDepositsRemaining is not 0 create
                    // new deposit record called last year dept with this value totalDepositsRemaining 
                    
                    $paymentRecords = Payment::forYear($fromYear)->where('unit_id', $unit->id)->latest()->get();
                    $totalPaymentsWithoutDeposit = $paymentRecords->sum('amount') - $totalDepositsPaid;

                    $balanceDifference = $totalPaymentsWithoutDeposit - $totalExpense;

                    // if balanceDifference > 0 then set starting_balance to this value, but if it is negative then
                    // put 0 in it and create new dept for this unit called last year dets. but in last year save
                    // it in negative form 
                    

                    YearlyBalance::updateOrCreate(
                        ['unit_id' => $unit->id, 'financial_year' => $fromYear],
                        [
                            'ending_balance'  => $balanceDifference,
                            'ending_deposits' => $totalDepositsPaid,
                        ]
                    );

                    $starting_balance = max($balanceDifference, 0); 

                    YearlyBalance::updateOrCreate(
                        ['unit_id' => $unit->id, 'financial_year' => $toYear],
                        [
                            'starting_balance'  => $starting_balance,
                            'starting_deposits' => $totalDepositsPaid,
                        ]
                    );

                    if ($balanceDifference < 0) {
                        $carryOverExpense = Expense::firstOrCreate(
                            [
                                'title'          => "بدهی انتقالی سال {$fromYear}",
                                'financial_year' => $toYear,
                                'is_charge'      => false,
                            ],
                            [
                                'total_amount'   => abs($balanceDifference),
                                'shares_count'   => 1,         
                                'date_from'      => now(),     
                                'payer_type'   => 'owner',
                                'unit_ids'       => [$unit->id],     
                            ]
                        );
                        
                        $carryOverExpense->updateQuietly(['financial_year' => $toYear]);

                        $detail = UnitExpenseDetail::create([
                            'unit_id'      => $unit->id,
                            'payer_type'   => 'owner',
                            'amount_due'   => abs($balanceDifference),
                            'is_paid'      => false,
                            'financial_year'  => $toYear,
                            'expense_id'   => $carryOverExpense->id,
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
                }
            });

            $setting = Setting::updateOrCreate(
                ['key' => 'financial_year', 'value' => $toYear],
                ['is_active' => true]
            );
            $setting->makeActive(); 
        });
    }

    // private function endingBalance(Unit $unit, int $year, int $starting): int
    // {
    //     $payments = $unit->payments()
    //         ->where('financial_year', $year)
    //         ->sum('amount');

    //     $expenses = $unit->unitExpenseDetails()
    //         ->whereHas('expense', fn ($q) => $q->where('financial_year', $year))
    //         ->sum('amount_due');

    //     return $starting + ($payments - $expenses);
    // }

    // private function endingDeposits(Unit $unit, int $year, int $starting): int
    // {
    //     $paidThisYear = $unit->deposits()
    //         ->where('financial_year', $year)
    //         ->sum(fn ($d) => $d->paymentUsages->sum('amount_used'));

    //     return $starting + $paidThisYear;
    // }

    // private function debtSum(int $year, bool $isCharge): int
    // {
    //     return UnitExpenseDetail::query()
    //         ->where('financial_year', $year)
    //         ->where('is_paid', false)
    //         ->whereHas('expense', fn ($q) => $q->where('is_charge', $isCharge))
    //         ->sum(DB::raw('amount_due - (
    //             SELECT COALESCE(SUM(amount_used),0)
    //             FROM payment_usages
    //             WHERE payable_type = "App\\\\Models\\\\UnitExpenseDetail"
    //               AND payable_id   = unit_expense_details.id
    //         )'));
    // }

    private function createAggregatedExpense(string $title, int $amount, int $year, bool $isCharge): void
    {
        $expense = Expense::create([
            'title'           => $title,
            'total_amount'    => $amount,
            'financial_year'  => $year,
            'is_charge'       => $isCharge,
            'shares_count'    => Unit::count(),
            'date_from'       => now(),
        ]);

        $perUnit = intdiv($amount, Unit::count());

        Unit::each(function (Unit $unit) use ($expense, $perUnit, $year) {
            UnitExpenseDetail::create([
                'unit_id'        => $unit->id,
                'expense_id'     => $expense->id,
                'financial_year' => $year,
                'amount_due'     => $perUnit,
                'is_paid'        => false,
                'payer_type'     => 'owner',
            ]);
        });
    }
}
