@props([
    'gitRepository' => '',
    'gitBranch' => '',
    'gitCommitHash' => '',
    'actions' => [],
])

<x-filament-panels::page>
    @if (session('flash_message'))
        <x-filament::card>
            {{ session('flash_message') }}
        </x-filament::card>
    @endif

    <x-filament::card title="設定">

        <div class="mb-4">
            <h3 class="font-bold">取り込み済の内容</h3>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th scope="col" class="px-6 py-3">リポジトリ</th>
                    <th scope="col" class="px-6 py-3">ブランチ</th>
                    <th scope="col" class="px-6 py-3">コミットハッシュ</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="px-6 py-4">{{ $gitRepository }}</td>
                    <td class="px-6 py-4">{{ $gitBranch }}</td>
                    <td class="px-6 py-4">{{ $gitCommitHash }}</td>
                </tr>
            </tbody>
        </table>

    </x-filament::card>

</x-filament-panels::page>
