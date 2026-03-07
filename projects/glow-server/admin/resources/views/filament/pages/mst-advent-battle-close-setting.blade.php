<x-filament-panels::page>
    <x-filament::card>
        <form wire:submit="update">
            {{ $this->form }}
        </form>
    </x-filament::card>
</x-filament-panels::page>
