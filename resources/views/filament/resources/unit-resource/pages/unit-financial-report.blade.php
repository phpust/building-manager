<x-filament::page>
    @if($this->hasTenantInCurrentYear())
        <div class="flex gap-4 mb-4">
            <x-filament::button wire:click="$set('filter', 'owner')">
                مالک
            </x-filament::button>

            <x-filament::button wire:click="$set('filter', 'tenant')">
                 مستأجر
            </x-filament::button>

            <x-filament::button wire:click="$set('filter', 'all')">
                همه
            </x-filament::button>
        </div>
    @endif

    <livewire:unit-expense-table
        :unit="$this->record"
        :filter="$this->filter"
        :tenant-id="$this->tenantId"
        wire:key="expenses-{{ $this->record->id }}-{{ $this->filter }}-{{ $this->tenantId }}"
    />
</x-filament::page>
