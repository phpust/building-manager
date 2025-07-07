@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto p-4">

    {{-- انتخاب واحد --}}
    <form method="GET" action="{{ route('unit.report') }}" class="mb-6">
        <label for="unit_id" class="block text-sm font-medium mb-1">انتخاب واحد:</label>
        <select name="unit_id" id="unit_id"
                class="border border-gray-300 rounded-md p-2 w-full sm:w-1/2"
                onchange="this.form.submit()">
            <option value="">-- انتخاب واحد --</option>
            @foreach ($units as $unit)
                <option value="{{ $unit->id }}" {{ request('unit_id') == $unit->id ? 'selected' : '' }}>
                    واحد {{ $unit->number ?? '-' }} – طبقه {{ $unit->floor ?? '-' }}
                </option>
            @endforeach
        </select>
    </form>

    @if ($selectedUnit)

        <div>
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
        </div>

        {{-- گزارش مالی واحد --}}
        @livewire(
            'unit-expense-table',
            [
                'unit'      => $selectedUnit,
                'filter'    => 'all',     // مقدار اولیه
                'tenantId'  => null,
            ],
            key('expenses-'.$selectedUnit->id)
        )

    @else
        <p class="text-gray-600">لطفاً یک واحد را از لیست بالا انتخاب کنید.</p>
    @endif

</div>
@endsection
