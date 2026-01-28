<x-filament-panels::page>
    <x-filament-panels::form
        :wire:key="$this->getId() . '.forms.' . $this->getFormStatePath()">
        <span class="font-bold">選択した年月を対象とします</span>
        <span style="margin-left: 1rem;">※時差都合の為、対象月の3日前後のデータも含みます</span>
        <span style="margin-left: 1rem;">※日本時間を基準に取得します</span>
        <span style="margin-left: 1rem;">※出力されるのはExcelファイル(.xlsx)となります</span>
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </x-filament-panels::form>
</x-filament-panels::page>


