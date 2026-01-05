<x-filament-panels::page>
    <x-filament-panels::form :wire:key="$this->getId() . '.forms.' . $this->getFormStatePath()">
        {{ $this->form }}
        <div class="flex justify-end">
            <x-filament::button wire:click='update' class="w-32">更新</x-filament::button>
        </div>
    </x-filament-panels::form>
</x-filament-panels::page>
