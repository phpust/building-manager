<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Str;
use App\Models\Unit;
use App\Models\UnitExpenseDetail;
use App\Models\Deposit;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\YearlyBalance;
use Carbon\Carbon;
use App\Models\User;

class UnitExpenseTable extends Component
{
    public Unit $unit;
    public string $filter = 'all';
    public ?int $tenantId = null;

    public $ownersThisYear = [];
    public $tenantsThisYear = [];


    public function mount(Unit $unit)
    {
        $this->unit = $unit;
        
        $this->ownersThisYear = $unit->ownersInFinancialYear()
            ->with('user')   
            ->get();

        $this->tenantsThisYear = $unit->tenantsInFinancialYear()
            ->with('user') 
            ->get();
    }

    public function query()
    {
        $query = UnitExpenseDetail::query()
            ->where('unit_id', $this->unit->id);

        match ($this->filter) {
            'owner'  => $query->where('payer_type', 'owner'),
            'tenant' => $query->where('payer_type', 'tenant'),
            default  => null,
        };

        return $query;
    }

    public function getDeposits($year = null)
    {
        $query = Deposit::query()->where('unit_id', $this->unit->id);
        
        if($year){
            $query->forYear($year);
        }
        
        match ($this->filter) {
            'owner'  => $query->where('payer_type', 'owner'),
            'tenant' => $query->where('payer_type', 'tenant'),
            default  => null,
        };

        return $query->get();
    }

    public function getPayments()
    {
        $query = Payment::query()->where('unit_id', $this->unit->id);

        match ($this->filter) {
            'owner'  => $query->where('payer_type', 'owner'),
            'tenant' => $query->where('payer_type', 'tenant'),
            default  => null,
        };

        return $query->latest()->get();
    }

    protected function getSummary(): array
    {
        $fy = Setting::financialYear();
        $expenses      = $this->query()->get();        
        $charge   = $expenses->filter(fn($e) => $e->expense->is_charge ?? false);
        $other    = $expenses->reject(fn($e) => $e->expense->is_charge ?? false);

        
        $depositRecords = $this->getDeposits();
        $paymentRecords = $this->getPayments();

        $depositsPaid   = $depositRecords->sum(fn($d) => $d->paymentUsages->sum('amount_used'));
        $chargePaid     = $charge->where('is_paid', true)->sum('amount_due');


        /////////////////////////////////////////////////   Unit   /////////////////////////////////////////////////////
        $lastYearBalance = YearlyBalance::where('unit_id', $this->unit->id)
                               ->where('financial_year', $fy - 1)
                               ->first();

        $openingBalance        = $lastYearBalance?->ending_balance   ?? 0;
        $openingDepositsPaid   = $lastYearBalance?->ending_deposits_paid  ?? 0;
        $openingDepositsRemain   = $lastYearBalance?->ending_deposits_remaining  ?? 0;


        $totalOpening = YearlyBalance::where('financial_year', $fy - 1)
            ->selectRaw('SUM(ending_balance)   as balance, ' .
                        'SUM(ending_deposits_paid)      as dep_paid, ' .
                        'SUM(ending_deposits_remaining) as dep_rem')
            ->first();

        return [
            'chargeRecords'         => $charge->sortBy(fn($e) => $e->expense->date_from),
            'otherExpenseRecords'   => $other->sortBy(fn($e) => $e->expense->date_from),
            'depositRecords'        => $depositRecords,
            'paymentRecords'        => $paymentRecords,

            'totalPayments'                 => $paymentRecords->sum('amount'),
            'totalDepositsPaid'             => $depositsPaid,
            'totalPaymentsWithoutDeposit'   => $paymentRecords->sum('amount') - $depositsPaid,
            'totalChargeRequired'           => $charge->sum('amount_due'),
            'totalChargePaid'               => $chargePaid,
            'totalChargeRemaining'          => $charge->where('is_paid', false)->sum(fn ($e) => $e->amount_due - $e->paymentUsages->sum('amount_used')),
            'totalExpense'                  => $other->sum('amount_due') - ($openingBalance < 0 ? abs($openingBalance) : 0),
            'totalDeposits'                 => $depositRecords->sum('amount'),
            'totalDepositsRemaining'        => $depositRecords->sum('amount') - $depositsPaid,

            'openingBalance'         => $openingBalance,
            'openingDepositsPaid'    => $openingDepositsPaid,
            'openingDepositsRemain'  => $openingDepositsRemain,

            'totalOpeningBalance'        => $totalOpening->balance   ?? 0,
            'totalOpeningDepositsPaid'   => $totalOpening->dep_paid  ?? 0,
            'totalOpeningDepositsRemain' => $totalOpening->dep_rem   ?? 0,
        ];
    }

    public function render()
    {
        return view('livewire.unit-expense-table', $this->getSummary());

    }
}
