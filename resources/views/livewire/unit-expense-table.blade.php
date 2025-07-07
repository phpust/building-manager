@php
    // hazine sale jari
    $totalExpenseWithPreviousYear = $totalExpense;

    // + mande bedehi sale ghabl
    if ($openingBalance < 0) {
        $totalExpenseWithPreviousYear += abs($openingBalance);
    }

    // majmue varizi ha ( bejoz vadie ha ) - majmue hazine ha va bedehi sale ghabl
    $netBalance = $totalPaymentsWithoutDeposit - $totalExpenseWithPreviousYear;
    
    // dar surate vojude hazine meghdaresh mire tu otherDue
    $otherDue  = $netBalance < 0 ? abs($netBalance) : 0;
    
    if ($totalChargeRemaining > $otherDue) {
        $otherDue = 0;
    }else{
        $totalChargeRemaining = 0;
    }

    // age majmue varizi mosbat bud va bishtare az chargaye nadade bud 
    $creditInFund = max($netBalance - $totalChargeRemaining, 0);

    // majmue bedehi gheir az vadie
    // bishtarin mablagh beine vaziat sandogh va charge haye pardakht nashode
    $debtDue      = $totalChargeRemaining + $otherDue;

    // majmue bedehi ba vadie
    $totalFinalDue = $debtDue + $totalDepositsRemaining;

@endphp

<div class="overflow-x-auto">
    @if ($unit->hasTenantInFinancialYear())
        <div class="flex gap-4 mb-4" x-data>
            <x-filament::button @click="$wire.set('filter', 'owner')">
                مالک
            </x-filament::button>

            <x-filament::button @click="$wire.set('filter', 'tenant')">
                مستأجر
            </x-filament::button>

            <x-filament::button @click="$wire.set('filter', 'all')">
                همه
            </x-filament::button>
        </div>
    @endif

    <div class="mb-6 bg-white p-4 rounded-xl shadow border border-gray-200">
        <h2 class="text-lg font-bold text-gray-800 mb-3">
            🏢 واحد {{ $unit->number ?? '—' }}
            – طبقه {{ $unit->floor ?? '—' }}
            @if(!empty($unit->description))
                - {{ $unit->description }} 
            @endif
        </h2>
        @auth
        <h2 class="text-lg font-bold text-gray-800 mb-3">👥 اطلاعات مالکین و مستأجران در سال {{ \App\Models\Setting::financialYear() }}</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div>
                <div class="font-semibold mb-1">📌 مالکین</div>
                @forelse($ownersThisYear as $owner)
                    <div>
                        {{ $owner->user->name }}
                        <span class="text-gray-500">
                            (از {{ jdate($owner->from_date)->format('Y/m/d') }}
                            @if($owner->to_date)
                                تا {{ jdate($owner->to_date)->format('Y/m/d') }}
                            @else
                                تاکنون
                            @endif
                            )
                        </span>
                    </div>
                @empty
                    <div class="text-gray-400">اطلاعی ثبت نشده است.</div>
                @endforelse
            </div>

            <div>
                <div class="font-semibold mb-1">🏠 مستأجران</div>
                @forelse($tenantsThisYear as $tenant)
                    <div>
                        {{ $tenant->user->name }}
                        <span class="text-gray-500">
                            (از {{ jdate($tenant->from_date)->format('Y/m/d') }}
                            @if($tenant->to_date)
                                تا {{ jdate($tenant->to_date)->format('Y/m/d') }}
                            @else
                                تاکنون
                            @endif
                            )
                        </span>
                    </div>
                @empty
                    <div class="text-gray-400">اطلاعی ثبت نشده است.</div>
                @endforelse
            </div>
        </div>
        @endauth
    </div>

    <div class="bg-white p-6 rounded-xl shadow border border-gray-200 mt-12">
        <h2 class="text-xl font-bold text-right text-gray-800 mb-4">🔍 وضعیت مالی شما</h2>
        
        <div class="text-right text-base mt-2 mb-4">
            در سال‌های گذشته شما مبلغ 
            <span class="font-extrabold text-green-700">{{ number_format($openingDepositsPaid) }}</span> ریال ودیعه پرداخت کرده‌اید.
        </div>

        
        <div class="text-right text-base mt-2 mb-4">
            در سال جاری 
            <span class="font-extrabold text-green-700">{{ number_format($totalDepositsPaid) }}</span> ریال ودیعه پرداخت کرده‌اید و 
            @if ($totalDepositsRemaining > 0)
                <span class="text-red-700 font-semibold">{{ number_format($totalDepositsRemaining) }}</span> ریال بدهکارید.
            @else
                بدهی ودیعه ندارید.
            @endif
        </div>

        <div class="text-right text-base mt-2 mb-4">
            بدون در نظر گرفتن ودیعه، شما در سال جاری مبلغ 
            <span class="font-extrabold text-green-700">{{ number_format($totalPaymentsWithoutDeposit) }}</span> ریال پرداخت کرده‌اید و با احتساب مانده از سال‌های قبل،
            @if ($netBalance > $totalChargeRemaining)
                <span class="text-green-700 font-semibold">{{ number_format($creditInFund) }}</span> ریال اضافه در صندوق دارید.
            @elseif ($netBalance < 0)
                
                <span class="text-red-700 font-semibold">{{ number_format($debtDue) }}</span> ریال بدهکار هستید.
            @else
                پرداخت‌های شما با هزینه‌ها برابر است.
            @endif
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm text-gray-700">
            <div class="bg-gray-50 p-4 rounded-lg">
                <div class="font-semibold mb-1">✅ پرداخت‌های شما</div>
                @if($openingBalance > 0)
                <div>
                    مانده انتقالی از سال قبل:
                    <strong class="{{ $openingBalance > 0 ? 'text-green-700' : 'text-red-600' }}">
                        {{ number_format($openingBalance) }} ریال
                    </strong>
                </div>
                @endif
                 <div>
                    شارژ پرداخت‌شده:
                    <strong class="{{ $totalChargePaid < $totalChargeRequired ? 'text-red-600' : 'text-green-700' }}">
                        {{ number_format($totalChargePaid) }} ریال
                    </strong>
                    (از <strong>{{ number_format($totalChargeRequired) }} ریال</strong>)
                </div>
                <div>
                    ودیعه پرداخت‌شده:
                    <strong>{{ number_format($totalDepositsPaid) }} ریال</strong>
                    (از <strong>{{ number_format($totalDeposits) }} ریال </strong> که  {{ number_format($openingDepositsRemain) }} ریال از سال‌های قبل می باشد)

                </div>
                <div>
                    سایر واریزی‌ها:
                    <strong>{{ number_format($totalPaymentsWithoutDeposit - $totalChargePaid - max($openingBalance, 0)) }} ریال</strong>
                </div>

                <div class="mt-1 border-t pt-2">
                    مجموع واریزی‌های مربوط به شما:
                    <strong>{{ number_format($totalPaymentsWithoutDeposit + $totalDepositsPaid) }} ریال</strong>
                </div>
            </div>

            @if($debtDue > 0)
            <div class="bg-gray-50 p-4 rounded-lg">
                <div class="font-semibold mb-1">🧾 سهم شما از هزینه‌ها</div>
                @if($openingBalance < 0)
                <div>
                    مانده بدهی انتقالی از سال قبل:
                    <strong class="text-red-600">{{ number_format(abs($openingBalance)) }} ریال</strong>
                </div>
                @endif

                <div>
                    مجموع هزینه‌های سال جاری:
                    <strong>{{ number_format($totalExpense) }} ریال</strong>
                </div>


                <div class="mt-1 border-t pt-2">
                    مجموع هزینه‌های قابل پرداخت:
                    <strong class="text-blue-800">{{ number_format($totalExpense + max(-$openingBalance, 0)) }} ریال</strong>
                </div>
            </div>
            @endif


            <div class="bg-gray-50 p-4 rounded-lg">
                <div class="font-semibold mb-1">📌 بدهی‌های باقی‌مانده</div>

                <div>
                    @if($totalChargeRemaining > 0)
                        شارژ:
                        <strong class="text-red-600">{{ number_format($totalChargeRemaining) }} ریال</strong>
                    @elseif($otherDue > 0)
                        هزینه‌ها:
                        <strong class="text-red-600">{{ number_format($otherDue) }} ریال</strong>
                    @endif
                </div>

                <div>
                    ودیعه: 
                    <strong class="{{ $totalDepositsRemaining ? 'text-red-600' : 'text-green-600' }}">
                        {{ number_format($totalDepositsRemaining) }} ریال
                    </strong>
                </div>

                <div class="mt-1 border-t pt-2">
                    مبلغ قابل پرداخت: 
                    <strong class="text-blue-800">{{ number_format($totalFinalDue) }} ریال</strong>
                </div>
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
                <th class="px-4 py-2 text-right">پیوست</th>
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
                    <td class="px-4 py-2 text-center">
                        @if (!empty($expense->expense->attachment))
                            <button 
                                x-data 
                                @click="$dispatch('show-attachment', { url: '{{ asset('storage/' . $expense->expense->attachment) }}' })" 
                                class="text-blue-600 hover:text-blue-800">
                                <x-heroicon-o-eye class="w-5 h-5" />
                            </button>
                        @else
                            <span class="text-gray-400">—</span>
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

<div x-data="{ isOpen: false, attachmentUrl: '' }"
     x-on:show-attachment.window="attachmentUrl = $event.detail.url; isOpen = true">

    <div x-show="isOpen"
         x-cloak
         class="fixed inset-0 z-50 bg-black/60 overflow-auto"
         @click.away="isOpen = false">

        <!-- ظرف مدال با margin عمودی (برای موبایل) -->
        <div class="relative mx-auto my-6 w-11/12 sm:w-10/12 lg:w-8/12 xl:w-7/12
                    bg-white rounded-lg shadow-lg">

            <!-- سربرگ ثابت با ارتفاع 56px -->
            <div class="flex justify-between items-center p-3 border-b h-14">
                <h3 class="text-base font-semibold">📎 پیوست هزینه</h3>

                <a :href="attachmentUrl"
                   download
                   class="text-sm text-blue-600 hover:underline mr-auto">
                    دانلود فایل
                </a>

                <button @click="isOpen = false"
                        class="text-gray-500 hover:text-gray-800 text-xl">
                    &times;
                </button>
            </div>

            <!-- ناحیهٔ نمایش فایل: ارتفاعِ باقیماندهٔ صفحه -->
            <div :style="`height: calc(90vh - 56px)`">
                <iframe :src="attachmentUrl"
                        class="w-full h-full"
                        frameborder="0">
                </iframe>
            </div>
        </div>
    </div>
</div>



</div>

