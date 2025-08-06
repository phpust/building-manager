<?php

namespace App\Filament\Resources\PaymentResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\BuildingYearlyTotal;
use App\Models\YearlyBalance;
use App\Models\PaymentUsage;
use App\Models\Deposit;
use App\Models\Payment;
use App\Models\Expense;
use App\Models\Setting;

class PaymentSummary extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $year = Setting::financialYear();
        $lastYear = $year - 1;

        $balances = YearlyBalance::where('financial_year', $year)->get();
        $buildingYearlyTotal = BuildingYearlyTotal::where('financial_year', $lastYear)->first();
        
        $openingBalance = $buildingYearlyTotal['total_ending_balance'] ?? 0;
        
        $openingDeposits = $buildingYearlyTotal['total_deposits_paid'] ?? 0;
        $openingDepositsRemain = $buildingYearlyTotal['total_deposits_remaining'] ?? 0;

        $currentYearDeposits = PaymentUsage::where('payable_type', Deposit::class)
            ->whereHasMorph(
                'payable',
                [Deposit::class],
                function ($query) use ($year) {
                    $query->where('financial_year', $year);
                }
            )
            ->sum('amount_used');

        $currentYearPayments = Payment::where('type', 'normal')->sum('amount');
        $lastYearPayments = Payment::query()->where('description','like','انتقال مانده از سال %')->sum('amount');
        
        $currentYearPayments = $currentYearPayments - $currentYearDeposits - $lastYearPayments;

        $currentYearExpenses = Expense::query()->where('is_charge', '0')->sum('total_amount');
        $lastYearExpenses = Expense::query()->where('is_charge', '0')->where('title','like','بدهی انتقالی سال%')->sum('total_amount');
        
        $currentYearExpenses = $currentYearExpenses - $lastYearExpenses;

        $fundBalanceWithoutDeposits = $currentYearPayments + $lastYearPayments
                                        - $currentYearExpenses - $lastYearExpenses;
        // dd($openingBalance , $currentYearPayments , $currentYearExpenses , $lastYearExpenses);

        $fundBalanceWithDeposits = $fundBalanceWithoutDeposits + $currentYearDeposits + $openingDeposits;

        return [
            Stat::make('مانده اول سال صندوق', number_format($openingBalance) . ' ریال')
                ->description("مانده پایان سال $lastYear")
                ->icon('heroicon-o-banknotes')
                ->color('gray'),

            Stat::make('مجموع واریزی‌های سال جاری', number_format($currentYearPayments) . ' ریال')
                ->description("واریزی‌های غیر ودیعه‌ای")
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success'),

            Stat::make('مجموع ودیعه‌های سال قبل', number_format($openingDeposits) . ' ریال')
                ->description("مطالبات ودیعه : ".number_format($openingDepositsRemain) . ' ریال')
                ->icon('heroicon-o-lock-closed')
                ->color('warning'),

            Stat::make('مجموع ودیعه‌های سال جاری', number_format($currentYearDeposits) . ' ریال')
                ->description("فقط ودیعه‌های جدید در سال $year")
                ->icon('heroicon-o-lock-closed')
                ->color('warning'),

            Stat::make('مجموع هزینه‌های سال جاری', number_format($currentYearExpenses) . ' ریال')
                ->description("خرج‌کردهای ثبت‌شده")
                ->icon('heroicon-o-arrow-up-tray')
                ->color('danger'),

            Stat::make('مانده بدون ودیعه', number_format($fundBalanceWithoutDeposits) . ' ریال')
                ->description("مانده قابل برداشت")
                ->icon('heroicon-o-wallet')
                ->color('primary'),

            Stat::make('مانده با احتساب ودیعه', number_format($fundBalanceWithDeposits) . ' ریال')
                ->description("شامل ودیعه‌های موجود")
                ->icon('heroicon-o-currency-dollar')
                ->color('info'),
        ];
    }

}
