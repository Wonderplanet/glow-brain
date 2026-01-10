<x-filament-panels::page>
    <x-filament-panels::form :wire:key="$this->getId() . '.forms.' . $this->getFormStatePath()">
        <span class="font-bold">選択された期間 +72時間(3日)に消費された有償一次通貨を対象とします</span>
        <span style="margin-left: 1rem;">※計算は日本時間を基準として行われます</span>
        <span style="margin-left: 1rem;">※出力されるのはExcelファイル(.xlsx)となります</span>
        <span style="margin-left: 1rem;">※有償一次通貨のみを集計対象とします。無償一次通貨は対象ではありません</span>

        <br />
        {{ $this->form }}
        <br />

        <x-filament-panels::form.actions :actions="$this->getCachedFormActions()" :full-width="$this->hasFullWidthFormActions()" />

    </x-filament-panels::form>
</x-filament-panels::page>
