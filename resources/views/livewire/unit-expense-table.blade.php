@php
    $balanceDifference = $totalPaymentsWithoutDeposit - $totalExpense;
    
@endphp
<div>
    <div class="bg-white p-6 rounded-xl shadow border border-gray-200 mt-6">
        <h2 class="text-xl font-bold text-right text-gray-800 mb-4">🔍 وضعیت مالی شما</h2>
        
        <div class="text-right text-base mt-2 mb-4">
            در حال حاضر 
            <span class="font-extrabold text-green-700">{{ number_format($totalDepositsPaid) }}</span> ریال  ودیعه پرداخت کرده اید و
            @if ($totalDepositsRemaining > 0)
                <span class="text-red-700 font-semibold">
                    {{ number_format($totalDepositsRemaining) }} 
                </span>
                ریال بابت ودیعه بدهکارید.
            @else
                بابت ودیعه بدهکار نیستید.
            @endif
        </div>
        <div class="text-right text-base mt-2 mb-4">
              بدون در نظر گرفتن ودیعه ، تا این لحظه مجموعاً
            <span class="font-extrabold text-green-700">{{ number_format($totalPaymentsWithoutDeposit) }}</span> ریال  واریزی داشته اید
            و 
            @if ($balanceDifference > 0)
                <span class="text-green-700 font-semibold">
                    {{ number_format($balanceDifference) }} 
                </span>
                ریال از شارژهای پرداختی شما در صندوق محفوظ است.
            @elseif ($balanceDifference < 0)
                هنوز
                <span class="text-red-700 font-semibold">
                     {{ number_format(abs($balanceDifference)) }}
                </span>
                 ریال بدهکار  می باشید.
            @else
                پرداخت‌های شما دقیقاً با مبلغ هزینه های جاری برابر است.
            @endif
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm text-gray-700">
            <div class="bg-gray-50 p-4 rounded-lg">
                <div class="font-semibold mb-1">✅ پرداخت‌های شما</div>
                <div>
                    شارژ پرداخت‌شده:
                    <strong class="{{ $totalPayments < $totalChargeRequired ? 'text-red-600' : 'text-green-700' }}">
                        {{ number_format($totalChargePaid) }} ریال
                    </strong>
                    ( از 
                    <strong>{{ number_format($totalChargeRequired) }} ریال</strong>
                    که باید پرداخت می شد  )
                </div>
                <div>
                    ودیعه پرداخت‌شده:
                    <strong>{{ number_format($totalDepositsPaid) }} ریال</strong>
                    (از <strong>{{ number_format($totalDeposits) }} ریال</strong> که باید پرداخت می‌شد)
                </div>
                <div>
                    سایر واریزی ها :
                    <strong>{{ number_format($totalPaymentsWithoutDeposit - $totalChargePaid ) }} ریال</strong>
                </div>
            </div>

            @if($balanceDifference < 0)
            <div class="bg-gray-50 p-4 rounded-lg">
                <div class="font-semibold mb-1">🧾 سهم شما از هزینه‌ها</div>

                <div>
                    مجموع واریزی های شما :
                    <strong>{{ number_format($totalPaymentsWithoutDeposit) }} ریال</strong>
                </div>

                <div>
                    مجموع سهم شما از کل هزینه‌ها:
                    <strong>{{ number_format($totalExpense) }} ریال</strong>
                </div>

                <div class="mt-1 border-t pt-2">
                    وضعیت حساب  شما : 
                    <strong class="text-blue-800">{{ number_format($balanceDifference) }} ریال</strong>
                </div>
            </div>
            @endif


            <div class="bg-gray-50 p-4 rounded-lg">
                <div class="font-semibold mb-1">📌 بدهی‌های باقی‌مانده که باید پرداخت شود</div>
                <div>
                    شارژ: 
                    <strong class="{{ $totalChargeRemaining ? 'text-red-600' : 'text-green-600' }}">
                        {{ number_format($totalChargeRemaining) }} ریال
                    </strong>
                </div>
                <div>
                    ودیعه  : 
                    <strong class="{{ $totalDepositsRemaining ? 'text-red-600' : 'text-green-600' }}">
                        {{ number_format($totalDepositsRemaining) }} ریال
                    </strong>
                </div>
                @if($balanceDifference < 0)
                <div>
                    سایر هزینه‌ها:
                    <strong class="{{ $balanceDifference ? 'text-red-600' : 'text-green-600' }}">
                        {{ number_format( - $balanceDifference ) }} ریال
                    </strong>
                </div>
                @endif
            </div>
        </div>
    </div>



    @if ($paymentRecords->count())
        <h3 class="mt-6 mb-2 text-right font-bold text-lg">لیست واریزی‌ها</h3>
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-right">مبلغ  (ریال)</th>
                    <th class="px-4 py-2 text-right">واریز کننده</th>
                    <th class="px-4 py-2 text-right">تاریخ  واریز</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @foreach ($paymentRecords as $payment)
                    <tr>
                        <td class="px-4 py-2">{{ number_format($payment->amount) }}</td>
                        <td class="px-4 py-2">
                            {{ $payment->payer_type === 'owner' ? 'مالک' : 'مستأجر' }}
                        </td>
                        <td class="px-4 py-2">
                            {{ jdate($payment->paid_at ?? $payment->created_at)->format('Y/m/d') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if ($depositRecords->count())
        <h3 class="mt-6 mb-2 text-right font-bold text-lg">لیست ودیعه‌ها</h3>
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-right">نوع ودیعه</th>
                    <th class="px-4 py-2 text-right">مبلغ (ریال)</th>
                    <th class="px-4 py-2 text-right">تاریخ</th>
                    <th class="px-4 py-2 text-right">وضعیت</th>
                    <th class="px-4 py-2 text-right">تاریخ پرداخت</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @foreach ($depositRecords as $deposit)
                    <tr>
                        <td class="px-4 py-2">{{ $deposit->title ?? 'ودیعه' }}</td>
                        <td class="px-4 py-2">{{ number_format($deposit->amount) }}</td>
                        <td class="px-4 py-2">{{ jdate($deposit->created_at)->format('Y/m/d') }}</td>
                        <td class="px-4 py-2">
                            @if($deposit->is_paid)
                                <span class="text-green-600">پرداخت‌شده</span>
                            @else
                                <span class="text-red-600">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-2">
                            @php
                                $paymentDate = optional($deposit->paymentUsages->first()?->payment)->paid_at;
                            @endphp

                            @if($deposit->is_paid && $paymentDate)
                                <span class="text-green-600">{{ jdate($paymentDate)->format('Y/m/d') }}</span>
                            @else
                                <span class="text-red-600">-</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if ($chargeRecords->count())
        <h3 class="mt-6 mb-2 text-right font-bold text-lg">لیست شارژ ماهیانه</h3>
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-right">ماه / دوره</th>
                    <th class="px-4 py-2 text-right">مبلغ شارژ (ریال)</th>
                    <th class="px-4 py-2 text-right">تاریخ ثبت</th>
                    <th class="px-4 py-2 text-right">وضعیت</th>
                    <th class="px-4 py-2 text-right">تاریخ پرداخت</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @foreach ($chargeRecords as $charge)
                    <tr>
                        <td class="px-4 py-2">{{ $charge->expense->title }}</td>
                        <td class="px-4 py-2">{{ number_format($charge->amount_due) }}</td>
                        <td class="px-4 py-2">{{ jdate($charge->expense->date_from)->format('Y/m/d') }}</td>
                        <td class="px-4 py-2">
                            @if($charge->is_paid)
                                <span class="text-green-600">پرداخت‌شده</span>
                            @else
                                <span class="text-red-600">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-2">
                            @php
                                $payDate = optional($charge->paymentUsages->first()?->payment)->paid_at;
                            @endphp
                            {{ $charge->is_paid && $payDate ? jdate($payDate)->format('Y/m/d') : '-' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif


    <h3 class="mt-6 mb-2 text-right font-bold text-lg">لیست هزینه ها</h3>
    <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-2 text-right">شرح هزینه</th>
                <th class="px-4 py-2 text-right">کل مبلغ (ریال)</th>
                <th class="px-4 py-2 text-right">تقسیم شده بین</th>
                <th class="px-4 py-2 text-right">مبلغ (ریال)</th>
                <th class="px-4 py-2 text-right">تاریخ ثبت</th>
                <th class="px-4 py-2 text-right">بر عهده</th>
                <th class="px-4 py-2 text-right">وضعیت</th>
                <th class="px-4 py-2 text-right">تاریخ پرداخت</th>
            </tr>
        </thead>

        <tbody class="bg-white divide-y divide-gray-100">
            @forelse ($otherExpenseRecords as $expense)
                <tr>
                    <td class="px-4 py-2">{{ $expense->expense->title }}</td>
                    <td class="px-4 py-2">{{ number_format($expense->expense->total_amount) }}</td>
                    <td class="px-4 py-2">{{ $expense->expense->shares_count }} نفر</td>
                    <td class="px-4 py-2">{{ number_format($expense->amount_due) }}</td>
                    <td class="px-4 py-2">{{ jdate($expense->expense->date_from)->format('Y/m/d') }}</td>
                    <td class="px-4 py-2">
                        {{ $expense->payer_type === 'owner' ? 'مالک' : 'مستأجر' }}
                    </td>
                    <td class="px-4 py-2">
                        @if($expense->is_paid)
                            <span class="text-green-600">پرداخت‌شده</span>
                        @else
                            <span class="text-red-600">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-2">
                    @php
                        $paymentDate = optional($expense->paymentUsages->first()?->payment)->paid_at;
                    @endphp

                    @if($expense->is_paid && $paymentDate)
                        <span class="text-green-600">{{ jdate($paymentDate)->format('Y/m/d') }}</span>
                    @else
                        <span class="text-red-600">-</span>
                    @endif
                </td>

                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-6 text-center text-gray-400">
                        هیچ رکوردی یافت نشد.
                    </td>
                </tr>
            @endforelse

            @if($otherExpenseRecords->count())
                <tr class="bg-gray-50 font-bold text-sm text-gray-700">
                    <td class="px-4 py-3 text-right">جمع کل</td>
                    <td class="px-4 py-3 text-right">
                        {{ number_format($otherExpenseRecords->sum(fn($e) => $e->expense->total_amount)) }}
                    </td>
                    <td></td>
                    <td class="px-4 py-3 text-right">
                        {{ number_format($otherExpenseRecords->sum('amount_due')) }}
                    </td>
                    <td colspan="4"></td>
                </tr>
            @endif
        </tbody>
    </table>



</div>
