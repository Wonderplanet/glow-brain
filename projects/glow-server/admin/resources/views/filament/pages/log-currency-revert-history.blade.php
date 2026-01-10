<x-filament-panels::page>
    <form>
        {{ $this->form }}
        <x-filament::button wire:click='updateSearch' class="mt-2">検索</x-filament::button>
    </form>

    @if($userId)
    <x-filament::card>
        <!-- 検索した時にリストを表示する -->
        <!-- 初回はdispachでパラメータが渡っていないため、$userIdをここで渡す -->
        <livewire:log-currency-revert-history-list :userId="$userId" />
    </x-filament::card>
    @endif
</x-filament-panels::page>
