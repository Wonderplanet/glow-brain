<x-filament-panels::page>
    詳細情報
    {{ $this->infoList }}

    <x-filament-panels::form :wire:key="$this->getId() . '.searchForms.' . $this->getFormStatePath()">
        {{ $this->form }}
        <br />
        <x-filament-panels::form.actions :actions="$this->getFormActions()" />
    </x-filament-panels::form>
</x-filament-panels::page>
