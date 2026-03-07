<x-filament-panels::page>
    <x-filament::card>
        <form wire:submit.prevent="search">
            {{ $this->form }}
            <x-filament::button type="submit" wire:click='search' class="mt-2">検索</x-filament::button>
        </form>
    </x-filament::card>

    <livewire:delete-user-list />
</x-filament-panels::page>
