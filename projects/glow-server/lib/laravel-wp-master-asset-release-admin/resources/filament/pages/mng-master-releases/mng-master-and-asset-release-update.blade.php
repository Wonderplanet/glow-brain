<x-filament-panels::page>
    <span class="font-bold" style="color: deeppink">配信中のリリース情報</span>
    @foreach($currentData as $heading => $list)
        <x-filament-tables::container class="overflow-x-auto" style="margin-bottom: 0.5rem;">
            <span class="font-bold">{{$headingNames[$heading]}}</span>
            <x-filament-tables::table>
                <x-slot name="header">
                    <x-filament-tables::header-cell>リリースキー</x-filament-tables::header-cell>
                    <x-filament-tables::header-cell>クライアント互換性バージョン</x-filament-tables::header-cell>
                    <x-filament-tables::header-cell>メモ欄</x-filament-tables::header-cell>
                    <x-filament-tables::header-cell>データハッシュ</x-filament-tables::header-cell>
                    <x-filament-tables::header-cell>インポート実行日</x-filament-tables::header-cell>
                </x-slot>
                @foreach ($list as $record)
                    <div>
                        <x-filament-tables::row :recordAction="true">
                            <x-filament-tables::cell class="px-4 py-1 items-center" >
                                <span style="color: deeppink">{{$record['releaseKey']}}</span>
                            </x-filament-tables::cell>
                            <x-filament-tables::cell class="px-4 py-1 items-center" >
                                >= <span style="color: deeppink">{{$record['clientCompatibilityVersion']}}</span>
                            </x-filament-tables::cell>
                            <x-filament-tables::cell class="px-4 py-1 items-center" >
                                <span style="color: deeppink">{{$record['description']}}</span>
                            </x-filament-tables::cell>
                            <x-filament-tables::cell class="px-4 py-1 items-center" >
                                <span style="color: deeppink">{{$record['dataHash']}}</span>
                            </x-filament-tables::cell>
                            <x-filament-tables::cell class="px-4 py-1 items-center" >
                                <span style="color: deeppink">{{$record['importedAt']}}</span>
                            </x-filament-tables::cell>
                        </x-filament-tables::row>
                    </div>
                @endforeach
            </x-filament-tables::table>
        </x-filament-tables::container>
    @endforeach
    <br />

    <span class="font-bold">配信可能なリリース情報</span>
    @foreach($targetData as $heading => $list)
        <x-filament-tables::container class="overflow-x-auto" style="margin-bottom: 0.5rem;">
            <span class="font-bold">{{$headingNames[$heading]}}</span>
            <x-filament-tables::table>
                <x-slot name="header">
                    <x-filament-tables::header-cell>配信する</x-filament-tables::header-cell>
                    <x-filament-tables::header-cell>リリースキー</x-filament-tables::header-cell>
                    <x-filament-tables::header-cell>クライアント互換性バージョン</x-filament-tables::header-cell>
                    <x-filament-tables::header-cell>メモ欄</x-filament-tables::header-cell>
                    <x-filament-tables::header-cell>データハッシュ</x-filament-tables::header-cell>
                    <x-filament-tables::header-cell>インポート実行日</x-filament-tables::header-cell>
                </x-slot>
                @foreach ($list as $record)
                    <div>
                        <x-filament-tables::row :recordAction="true">
                            <x-filament-tables::cell class="px-4 py-1 items-center" >
                                <x-filament::input.checkbox wire:click="onCheckMngReleaseId(`{{$heading}}`, `{{$record['id']}}`)"/>
                            </x-filament-tables::cell>
                            <x-filament-tables::cell class="px-4 py-1 items-center" >
                                {{$record['releaseKey']}}
                            </x-filament-tables::cell>
                            <x-filament-tables::cell class="px-4 py-1 items-center" >
                                >= {{$record['clientCompatibilityVersion']}}
                            </x-filament-tables::cell>
                            <x-filament-tables::cell class="px-4 py-1 items-center" >
                                {{$record['description']}}
                            </x-filament-tables::cell>
                            <x-filament-tables::cell class="px-4 py-1 items-center" >
                                {{$record['dataHash']}}
                            </x-filament-tables::cell>
                            <x-filament-tables::cell class="px-4 py-1 items-center" >
                                {{$record['importedAt']}}
                            </x-filament-tables::cell>
                        </x-filament-tables::row>
                    </div>
                @endforeach
            </x-filament-tables::table>
            @if(empty($list))
                <span>配信可能なリリース情報がありません</span>
            @endif
        </x-filament-tables::container>
    @endforeach
    <x-filament-panels::form.actions
        :actions="$this->getCachedFormActions()"
        :full-width="$this->hasFullWidthFormActions()"
    />
    <div class="mb"></div>
</x-filament-panels::page>
