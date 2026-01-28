<div class="flex flex-col items-center">
    @foreach($viewData as $title => $data)
        <span class="font-bold">--- {{ $title }} ---</span>
        @foreach($data as $status => $records)
            <span class="flex items-start font-bold" @if($status === 'applyData') style="color: deeppink" @endif>
                {{ $statusNames[$status] }}
            </span>
            <x-filament-tables::table class="mb-5 w-auto border">
                <x-slot name="header">
                    <x-filament-tables::header-cell>リリースキー</x-filament-tables::header-cell>
                    <x-filament-tables::header-cell>メモ欄</x-filament-tables::header-cell>
                    <x-filament-tables::header-cell>クライアント互換性バージョン</x-filament-tables::header-cell>
                    <x-filament-tables::header-cell>データハッシュ</x-filament-tables::header-cell>
                </x-slot>
                @foreach($records as $row)
                    <div>
                        <x-filament-tables::row :recordAction="true">
                            <x-filament-tables::cell class="px-4 py-1 items-center" >
                                <span @if($status === 'applyData') style="color: deeppink" @endif>
                                    {{ $row['releaseKey']}}
                                </span>
                            </x-filament-tables::cell>
                            <x-filament-tables::cell class="px-4 py-1 items-center" >
                                <span @if($status === 'applyData') style="color: deeppink" @endif>
                                    {{ $row['description'] }}
                                </span>
                            </x-filament-tables::cell>
                            <x-filament-tables::cell class="px-4 py-1 items-center">
                                <span @if($status === 'applyData') style="color: deeppink" @endif>
                                    >= {{ $row['client_version']}}
                                </span>
                            </x-filament-tables::cell>
                            <x-filament-tables::cell class="px-4 py-1 items-center">
                                <span @if($status === 'applyData') style="color: deeppink" @endif>
                                    {{ $row['dataHash']}}
                                </span>
                            </x-filament-tables::cell>
                        </x-filament-tables::row>
                    </div>
                @endforeach
            </x-filament-tables::table>
        @endforeach
        <br />
    @endforeach
</div>

