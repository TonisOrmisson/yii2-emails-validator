<x-filament-panels::page>
    {{-- the component keeps its own state: Livewire must not morph it --}}
    <div wire:ignore>
        @include('emailsvalidator::index')
    </div>
</x-filament-panels::page>
