<x-filament-panels::page>
    <x-filament-panels::form
        :wire:key="$this->getId() . '.forms.' . $this->getFormStatePath()">
        <span class="font-bold">リリース日〜選択した年月までを対象とします</span>
        <span style="margin-left: 1rem;">※日本時間を基準に取得します</span>
        <span style="margin-left: 1rem;">※出力されるのはExcelファイル(.xlsx)となります</span>
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </x-filament-panels::form>
</x-filament-panels::page>
