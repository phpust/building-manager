<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Unit;
use App\Models\UnitExpenseDetail;
use App\Models\Deposit;
use App\Models\Payment;
use App\Models\Setting;

class UnitExpenseTable extends Component
{
    public Unit $unit;
    public string $filter = 'all';
    public ?int $tenantId = null;

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

    public function getDeposits()
    {
        $query = Deposit::query()->where('unit_id', $this->unit->id);

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
        $allExpenses    = $this->query()->get();        
        $chargeRecords  = $allExpenses->filter(fn ($e) => $e->expense->is_charge ?? false)->sortBy(fn ($e) => $e->expense->date_from);
        $otherExpenses  = $allExpenses->reject(fn ($e) => $e->expense->is_charge ?? false)->sortBy(fn ($e) => $e->expense->date_from);

        $depositRecords = $this->getDeposits();
        $paymentRecords = $this->getPayments();

        $totalChargePaid   = $chargeRecords->where('is_paid', true)->sum('amount_due');
        $totalDepositsPaid = $depositRecords->sum(fn($d) => $d->paymentUsages->sum('amount_used'));

        return [
            'chargeRecords'         => $chargeRecords,
            'otherExpenseRecords'   => $otherExpenses,
            'depositRecords'        => $depositRecords,
            'paymentRecords'        => $paymentRecords,

            'totalPayments'                 => $paymentRecords->sum('amount'),
            'totalPaymentsWithoutDeposit'   => $paymentRecords->sum('amount') - $totalDepositsPaid,
            'totalExpense'                  => $otherExpenses->sum('amount_due'),
            'totalChargeRequired'           => $chargeRecords->sum('amount_due'),
            'totalChargePaid'               => $totalChargePaid,
            'totalChargeRemaining'          => $chargeRecords->where('is_paid', false)->sum(fn ($e) => $e->amount_due - $e->paymentUsages->sum('amount_used')),
            'totalDeposits'                 => $depositRecords->sum('amount'),
            'totalDepositsPaid'             => $totalDepositsPaid,
            'totalDepositsRemaining'        => $depositRecords->sum('amount') - $totalDepositsPaid,
        ];
    }

    public function render()
    {
        return view('livewire.unit-expense-table', $this->getSummary());

    }
}
