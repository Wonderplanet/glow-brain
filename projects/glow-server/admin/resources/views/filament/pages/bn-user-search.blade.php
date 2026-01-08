<x-filament-panels::page>
    <x-filament::card>
        <h2 class="font-semibold text-gray-900">お客様公開済みお知らせ</h2>
        <livewire:bn-user-search-info :key="'info-'.now()" />
    </x-filament::card>
    <x-filament::card>
        <h2 class="font-semibold text-gray-900">プレイヤー検索</h2>
        <br />
        <form wire:submit.prevent="search">
            {{ $this->form }}
            <x-filament::button type="submit" wire:click='search' class="mt-2">検索</x-filament::button>
        </form>
    </x-filament::card>

    <livewire:user-search-list />
</x-filament-panels::page>
