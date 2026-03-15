@props([
'tableRows' => [],
])

<x-filament-panels::page>

    @if ($this->dataExist === true)
        <x-filament-tables::container>
            <div class="fi-ta-content relative divide-y divide-gray-200 overflow-x-auto dark:divide-white/10 dark:border-t-white/10">
                <x-filament-tables::table>
                    <x-slot name="header">
                        <x-filament-tables::header-cell></x-filament-tables::header-cell>
                        <x-filament-tables::header-cell>ステータス</x-filament-tables::header-cell>
                        <x-filament-tables::header-cell>開始日時（JST）</x-filament-tables::header-cell>
                        <x-filament-tables::header-cell>終了日時（JST）</x-filament-tables::header-cell>
                        <x-filament-tables::header-cell>メンテ文言</x-filament-tables::header-cell>
                    </x-slot>
                    <div>
                        @foreach ($tableRows as $row)
                            <x-filament-tables::row>

                                <x-filament-tables::cell>
                                    <x-filament::actions
                                        :actions="$row['actions']"
                                        />
                                </x-filament-tables::cell>

                                <x-filament-tables::cell>
                                    <div class="fi-ta-text grid gap-y-1 px-3 py-3 fi-ta-text-item inline-flex items-center gap-1.5 text-sm text-gray-950">
                                        <x-filament::badge :color="$row['statusBadgeColor']"
                                            >{{ $row['statusContent'] }}</x-filament::badge>
                                    </div>
                                </x-filament-tables::cell>

                                <x-filament-tables::cell>
                                    <div class="fi-ta-text grid gap-y-1 px-3 py-3 fi-ta-text-item inline-flex items-center gap-1.5 text-sm text-gray-950">
                                        {{ $row['startAt'] }}
                                    </div>
                                </x-filament-tables::cell>

                                <x-filament-tables::cell>
                                    <div class="fi-ta-text grid gap-y-1 px-3 py-3 fi-ta-text-item inline-flex items-center gap-1.5 text-sm text-gray-950">
                                        {{ $row['endAt'] }}
                                    </div>
                                </x-filament-tables::cell>

                                <x-filament-tables::cell>
                                    <div class="fi-ta-text grid gap-y-1 px-3 py-3 fi-ta-text-item inline-flex items-center gap-1.5 text-sm text-gray-950">
                                        {{ $row['text'] }}
                                    </div>
                                </x-filament-tables::cell>

                            </x-filament-tables::row>
                        @endforeach
                    </div>
                </x-filament-tables::table>
            </div>
        </x-filament-tables::container>
    @else
        <x-filament::card>
            <div>
                全体メンテナンスは設定されていません。
            </div>
        </x-filament::card>
    @endif
</x-filament-panels::page>
