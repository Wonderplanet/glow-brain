<x-filament-panels::page>
    <x-filament-panels::form :wire:key="$this->getId() . '.forms.' . $this->getFormStatePath()">
        {{ $this->form }}
        <br />
        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </x-filament-panels::form>
</x-filament-panels::page>
