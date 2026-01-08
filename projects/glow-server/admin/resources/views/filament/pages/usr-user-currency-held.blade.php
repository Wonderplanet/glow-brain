<x-filament-panels::page>
    <x-filament::card>
        <form>
            {{ $this->form }}
            <x-filament::button wire:click='updateUserId' class="mt-2">検索</x-filament::button>
        </form>
    </x-filament::card>

    @if($userId)
    <x-filament::card>
            <livewire:usr-user-currency-held-over-view :userId="$userId"/>
    </x-filament::card>
    <x-filament::card>
        <p class="text-2xl">有償一次通貨：内訳</p>
        <br />
        <livewire:usr-currency-paid-list :userId="$userId"/>
    </x-filament::card>
    @endif
</x-filament-panels::page>
