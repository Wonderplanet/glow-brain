<x-filament-panels::page>
    {{$this->infoList}}
    <x-table :title="'パス効果'" :rows="$this->getEffectRows()" />
    {{$this->productInfoList}}
    <x-table :title="'プリズム配布'" :rows="$this->getAmountRows()" />
    {{$this->rewardTable()}}
</x-filament-panels::page>
