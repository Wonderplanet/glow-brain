<div>
    <x-filament::section collapsible>
        <x-slot name="heading">
            ユーザー情報検索
        </x-slot>

        <form wire:submit="search">
            {{ $this->form }}
            <x-filament::button wire:click='search' class="mt-2">検索</x-filament::button>
        </form>
    </x-filament::section>
</div>
