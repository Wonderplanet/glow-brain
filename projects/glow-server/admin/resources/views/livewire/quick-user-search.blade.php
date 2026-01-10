<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            MY_IDクイック検索
        </x-slot>

        <form wire:submit.prevent="search">
            <x-filament::input.wrapper style="display: inline-block">
                <x-filament::input type="search" wire:model="myId"/>
            </x-filament::input.wrapper>
            <x-filament::button wire:click='search' class="mt-2">検索</x-filament::button>
        </form>
        <div style="color: red;">{{$message}}</div>
    </x-filament::section>
</x-filament-widgets::widget>
