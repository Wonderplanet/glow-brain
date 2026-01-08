<x-filament-panels::page>
    {{-- テーブル表示 --}}
    <x-filament::card class="mt-6">
        <div class="mb-4">
            <h2 class="text-xl font-semibold text-gray-900">カムバックボーナス進行状況一覧</h2>
        </div>
        {{ $this->table }}
    </x-filament::card>
</x-filament-panels::page>
