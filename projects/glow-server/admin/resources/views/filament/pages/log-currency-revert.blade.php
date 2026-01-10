<x-filament-panels::page>
    <x-filament::card>
        <form>
            {{ $this->form }}
            <x-filament::button wire:click='updateSearch' class="mt-2">検索</x-filament::button>
        </form>
    </x-filament::card>

    @if($userId)
    <x-filament::card>
        <!-- 検索した時にリストを表示する -->
        <!-- 初回はdispachでパラメータが渡っていないため、$userIdをここで渡す -->
        <livewire:log-currency-revert.log-currency-revert-list
            :userId="$userId"
            :startDate="$startDate"
            :endDate="$endDate"
        />
    </x-filament::card>
    @endif
</x-filament-panels::page>
