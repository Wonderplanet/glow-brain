@props([
    'gitBranch' => '',
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
        <!-- <div class="m-4">
            <div>現在のGit操作ブランチ : {{$gitBranch}}</div>
            <div>現在DB適用中のコミットハッシュ : {{$hash}} </div>
        </div> -->
    </x-filament::card>
</x-filament-panels::page>
