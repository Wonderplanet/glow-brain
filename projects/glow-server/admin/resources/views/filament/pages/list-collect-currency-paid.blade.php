<x-filament-panels::page>
    <x-filament::card>
        <p class="text-2xl">有償一次通貨の回収</p>
        <br />
        <x-filament-panels::form :wire:key="$this->getId() . '.searchForms.' . $this->getFormStatePath()">
            <span style="margin-left: 1rem;">※回収結果は、各集計レポートにも反映されます</span>
            {{ $this->searchForm }}
            <br />
            <x-filament-panels::form.actions :actions="$this->getSearchFormActions()" />
        </x-filament-panels::form>
    </x-filament::card>

    @if($userId)
        <x-filament::card>
            <!-- 検索した時にリストを表示する -->
            <!-- 初回はdispachでパラメータが渡っていないため、$userIdをここで渡す -->
            <livewire:collect-currency-paid.collect-currency-paid-list
                :userId="$userId"
            />
        </x-filament::card>
    @endif
</x-filament-panels::page>
