<x-filament::page>
    <livewire:unit-expense-table
        :unit="$this->record"
        :filter="$this->filter"
        :tenant-id="$this->tenantId"
        wire:key="expenses-{{ $this->record->id }}-{{ $this->filter }}-{{ $this->tenantId }}"
    />
</x-filament::page>
