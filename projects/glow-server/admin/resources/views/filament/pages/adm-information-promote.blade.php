@props([
    'hash' => '',
    'actions' => [],
])

<x-filament-panels::page>
    @if (session('flash_message'))
        <x-filament::card>
            {{ session('flash_message') }}
        </x-filament::card>
    @endif

    <x-filament::card title="設定">
    </x-filament::card>
</x-filament-panels::page>
