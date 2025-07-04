<x-filament::page>
    <div class="max-w-xl mx-auto">
        {{ $this->form }}

        <x-filament::button
            wire:click="close"
            class="mt-6 w-full"
            color="danger"
            icon="heroicon-o-check-circle">
            بستن سال مالی و انتقال مانده‌ها
        </x-filament::button>
    </div>
</x-filament::page>
