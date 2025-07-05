<x-filament-widgets::widget>
    @if($hasUnit)
        @livewire(\App\Livewire\UnitExpenseTable::class, ['unit' => $unit])
    @else
        <div class="p-6 text-center text-gray-600">
            🔔 شما هیچ واحدی ندارید.
        </div>
    @endif
</x-filament-widgets::widget>