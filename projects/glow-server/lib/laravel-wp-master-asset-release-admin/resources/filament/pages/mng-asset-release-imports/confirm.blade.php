@use('WonderPlanet\Domain\MasterAssetReleaseAdmin\Enums\AssetDiffType')
<x-filament-panels::page>
    <x-filament::card>
        @if (isset($this->diffSizeAndroid))
        <p class="text-2xl">Android ({{ $this->diffSizeAndroid }})</p>
        <x-filament-tables::container class="overflow-x-auto">
            <x-filament-tables::table>
                <x-slot name="header">
                    <x-filament-tables::header-cell class="text-left"></x-tables::header-cell>
                        <x-filament-tables::header-cell class="text-left">{{ $this->fromEnvironment }} から {{ env('APP_ENV') }} へアセットをインポートします</x-tables::header-cell>
                </x-slot>
                <x-filament-tables::row :recordAction="true">
                    <x-filament-tables::cell class="px-4 py-1 font-bold">
                        Release Key
                    </x-filament-tables::cell>
                    <x-filament-tables::cell class="px-4 py-1">
                        {{ $this->androidAssetVersionInfoOfFromEnvironment['release_key'] }}
                    </x-filament-tables::cell>
                </x-filament-tables::row>
                <x-filament-tables::row :recordAction="true">
                    <x-filament-tables::cell class="px-4 py-1 font-bold">
                        Description
                    </x-filament-tables::cell>
                    <x-filament-tables::cell class="px-4 py-1">
                        {{ $this->androidAssetVersionInfoOfFromEnvironment['description'] }}
                    </x-filament-tables::cell>
                </x-filament-tables::row>
                <x-filament-tables::row :recordAction="true">
                    <x-filament-tables::cell class="px-4 py-1 font-bold">
                        Status
                    </x-filament-tables::cell>
                    <x-filament-tables::cell class="px-4 py-1">
                        @if ($this->androidAssetVersionInfoOfFromEnvironment['status'] === 3)
                        <span style='color: gray'>配信終了</span>
                        @elseif ($this->androidAssetVersionInfoOfFromEnvironment['status'] === 2)
                        <span style='color: darkolivegreen'>配信準備中</span>
                        @elseif ($this->androidAssetVersionInfoOfFromEnvironment['status'] === 1)
                        <span style='color: deeppink'>配信中</span>
                        @endif
                    </x-filament-tables::cell>
                </x-filament-tables::row>
                <x-filament-tables::row :recordAction="true">
                    <x-filament-tables::cell class="px-4 py-1 font-bold">
                        Git Revision
                    </x-filament-tables::cell>
                    <x-filament-tables::cell class="px-4 py-1">
                        @if ($this->releaseKeyAndroid !== $this->androidAssetVersionInfoOfEnvironment['release_key'])
                        (設定なし) →
                        @else
                        {{ $this->androidAssetVersionInfoOfEnvironment['git_revision'] }} →
                        @endif
                        <span style="color: deeppink">{{ $this->androidAssetVersionInfoOfFromEnvironment['git_revision'] }}</span>
                    </x-filament-tables::cell>
                </x-filament-tables::row>
                <x-filament-tables::row :recordAction="true">
                    <x-filament-tables::cell class="px-4 py-1 font-bold">
                        Catalog Hash
                    </x-filament-tables::cell>
                    <x-filament-tables::cell class="px-4 py-1">
                        @if ($this->releaseKeyAndroid !== $this->androidAssetVersionInfoOfEnvironment['release_key'])
                        (設定なし) →
                        @else
                        {{ $this->androidAssetVersionInfoOfEnvironment['catalog_hash'] }} →
                        @endif
                        <span style="color: deeppink">{{ $this->androidAssetVersionInfoOfFromEnvironment['catalog_hash'] }}</span>
                    </x-filament-tables::cell>
                </x-filament-tables::row>
                <x-filament-tables::row :recordAction="true">
                    <x-filament-tables::cell class="px-4 py-1 font-bold">
                        Total Bytes
                    </x-filament-tables::cell>
                    <x-filament-tables::cell class="px-4 py-1">
                        @if ($this->releaseKeyAndroid !== $this->androidAssetVersionInfoOfEnvironment['release_key'])
                        (設定なし) →
                        @else
                        {{ $this->androidAssetTotalBytesEnvironment }} →
                        @endif
                        <span style="color: deeppink">{{ $this->androidAssetTotalBytesFromEnvironment }}</span>
                    </x-filament-tables::cell>
                </x-filament-tables::row>
            </x-filament-tables::table>
        </x-filament-tables::container>
        <br />
        @if ($this->releaseKeyAndroid !== $this->androidAssetVersionInfoOfEnvironment['release_key'])
        <span style='color: darkred'>対象のreleaseKeyのアセット情報が無いため配信中の最新releaseKey: {{ $this->androidAssetVersionInfoOfEnvironment['release_key'] }}との差分を表示中</span>
        @endif
        <x-filament-tables::container class="overflow-x-auto">
            <x-filament-tables::table>
                <x-slot name="header">
                    <x-filament-tables::header-cell class="text-left"></x-tables::header-cell>
                        <x-filament-tables::header-cell class="text-left">File</x-tables::header-cell>
                            <x-filament-tables::header-cell class="text-left">Size</x-tables::header-cell>
                </x-slot>
                @foreach ($this->diffAndroid as $diff)
                <x-filament-tables::row :recordAction="true">
                    <x-filament-tables::cell class="px-4 py-1">
                        @if ($diff['diff_type'] === AssetDiffType::DIFF_TYPE_CHANGE)
                        <x-filament::badge class="mx-1" color="primary"><span class="font-bold">変更</span></x-filament::badge>
                        @elseif ($diff['diff_type'] === AssetDiffType::DIFF_TYPE_DELETE)
                        <x-filament::badge class="mx-1" color="danger"><span class="font-bold">削除</span></x-filament::badge>
                        @elseif ($diff['diff_type'] === AssetDiffType::DIFF_TYPE_ADD)
                        <x-filament::badge class="mx-1" color="success"><span class="font-bold">新規</span></x-filament::badge>
                        @endif
                    </x-filament-tables::cell>
                    <x-filament-tables::cell class="px-4 py-1">
                            <span style="{{$diff['color']}}">{{ $diff['file'] }}
                    </x-filament-tables::cell>
                    <x-filament-tables::cell class="px-4 py-1">
                        @if ($diff['diff_type'] === AssetDiffType::DIFF_TYPE_CHANGE)
                        <span style="{{$diff['color']}}">{{ $diff['size_format_output'] }} → {{ $diff['size_format_input'] }}
                            @else
                                <span style="{{$diff['color']}}">{{ $diff['size_format'] }}
                            @endif
                    </x-filament-tables::cell>
                </x-filament-tables::row>
                @endforeach
            </x-filament-tables::table>
        </x-filament-tables::container>
        <br />
        <br />
        @endif
        @if (isset($this->diffSizeIos))
        <p class="text-2xl">iOS ({{ $this->diffSizeIos }})</p>
        <x-filament-tables::container class="overflow-x-auto">
            <x-filament-tables::table>
                <x-slot name="header">
                    <x-filament-tables::header-cell class="text-left"></x-tables::header-cell>
                            <x-filament-tables::header-cell class="text-left">{{ $this->fromEnvironment }} から {{ env('APP_ENV') }} へアセットをインポートします</x-tables::header-cell>
                </x-slot>
                <x-filament-tables::row :recordAction="true">
                    <x-filament-tables::cell class="px-4 py-1 font-bold">
                        Release Key
                    </x-filament-tables::cell>
                    <x-filament-tables::cell class="px-4 py-1">
                        {{ $this->iosAssetVersionInfoOfFromEnvironment['release_key'] }}
                    </x-filament-tables::cell>
                </x-filament-tables::row>
                <x-filament-tables::row :recordAction="true">
                    <x-filament-tables::cell class="px-4 py-1 font-bold">
                        Description
                    </x-filament-tables::cell>
                    <x-filament-tables::cell class="px-4 py-1">
                        {{ $this->iosAssetVersionInfoOfFromEnvironment['description'] }}
                    </x-filament-tables::cell>
                </x-filament-tables::row>
                <x-filament-tables::row :recordAction="true">
                    <x-filament-tables::cell class="px-4 py-1 font-bold">
                        Status
                    </x-filament-tables::cell>
                    <x-filament-tables::cell class="px-4 py-1">
                        @if ($this->iosAssetVersionInfoOfFromEnvironment['status'] === 3)
                        <span style='color: gray'>配信終了</span>
                        @elseif ($this->iosAssetVersionInfoOfFromEnvironment['status'] === 2)
                        <span style='color: darkolivegreen'>配信準備中</span>
                        @elseif ($this->iosAssetVersionInfoOfFromEnvironment['status'] === 1)
                        <span style='color: deeppink'>配信中</span>
                        @endif
                    </x-filament-tables::cell>
                </x-filament-tables::row>
                <x-filament-tables::row :recordAction="true">
                    <x-filament-tables::cell class="px-4 py-1 font-bold">
                        Git Revision
                    </x-filament-tables::cell>
                    <x-filament-tables::cell class="px-4 py-1">
                        @if ($this->releaseKeyIos !== $this->iosAssetVersionInfoOfEnvironment['release_key'])
                        (設定なし) →
                        @else
                        {{ $this->iosAssetVersionInfoOfEnvironment['git_revision'] }} →
                        @endif
                        <span style="color: deeppink">{{ $this->iosAssetVersionInfoOfFromEnvironment['git_revision'] }}</span>
                    </x-filament-tables::cell>
                </x-filament-tables::row>
                <x-filament-tables::row :recordAction="true">
                    <x-filament-tables::cell class="px-4 py-1 font-bold">
                        Catalog Hash
                    </x-filament-tables::cell>
                    <x-filament-tables::cell class="px-4 py-1">
                        @if ($this->releaseKeyIos !== $this->iosAssetVersionInfoOfEnvironment['release_key'])
                        (設定なし) →
                        @else
                        {{ $this->iosAssetVersionInfoOfEnvironment['catalog_hash'] }} →
                        @endif
                        <span style="color: deeppink">{{ $this->iosAssetVersionInfoOfFromEnvironment['catalog_hash'] }}</span>
                    </x-filament-tables::cell>
                </x-filament-tables::row>
                <x-filament-tables::row :recordAction="true">
                    <x-filament-tables::cell class="px-4 py-1 font-bold">
                        Total Bytes
                    </x-filament-tables::cell>
                    <x-filament-tables::cell class="px-4 py-1">
                        @if ($this->releaseKeyIos !== $this->iosAssetVersionInfoOfEnvironment['release_key'])
                        (設定なし) →
                        @else
                        {{ $this->iosAssetTotalBytesEnvironment }} →
                        @endif
                        <span style="color: deeppink">{{ $this->iosAssetTotalBytesFromEnvironment }}</span>
                    </x-filament-tables::cell>
                </x-filament-tables::row>
            </x-filament-tables::table>
        </x-filament-tables::container>
        <br />
        @if ($this->releaseKeyIos !== $this->iosAssetVersionInfoOfEnvironment['release_key'])
        <span style='color: darkred'>対象のreleaseKeyのアセット情報が無いため配信中の最新releaseKey: {{ $this->iosAssetVersionInfoOfEnvironment['release_key'] }}との差分を表示中</span>
        @endif
        <x-filament-tables::container class="overflow-x-auto">
            <x-filament-tables::table>
                <x-slot name="header">
                    <x-filament-tables::header-cell class="text-left"></x-tables::header-cell>
                    <x-filament-tables::header-cell class="text-left">File</x-tables::header-cell>
                    <x-filament-tables::header-cell class="text-left">Size</x-tables::header-cell>
                </x-slot>
                @foreach ($this->diffIos as $diff)
                    <x-filament-tables::row :recordAction="true">
                        <x-filament-tables::cell class="px-4 py-1">
                            @if ($diff['diff_type'] === AssetDiffType::DIFF_TYPE_CHANGE)
                            <x-filament::badge class="mx-1" color="primary"><span class="font-bold">変更</span></x-filament::badge>
                            @elseif ($diff['diff_type'] === AssetDiffType::DIFF_TYPE_DELETE)
                            <x-filament::badge class="mx-1" color="danger"><span class="font-bold">削除</span></x-filament::badge>
                            @elseif ($diff['diff_type'] === AssetDiffType::DIFF_TYPE_ADD)
                            <x-filament::badge class="mx-1" color="success"><span class="font-bold">新規</span></x-filament::badge>
                            @endif
                        </x-filament-tables::cell>
                        <x-filament-tables::cell class="px-4 py-1">
                            <span style="{{$diff['color']}}">{{ $diff['file'] }}
                        </x-filament-tables::cell>
                        <x-filament-tables::cell class="px-4 py-1">
                            @if ($diff['diff_type'] === AssetDiffType::DIFF_TYPE_CHANGE)
                                <span style="{{$diff['color']}}">{{ $diff['size_format_output'] }} → {{ $diff['size_format_input'] }}
                            @else
                                <span style="{{$diff['color']}}">{{ $diff['size_format'] }}
                            @endif
                        </x-filament-tables::cell>
                    </x-filament-tables::row>
                @endforeach
            </x-filament-tables::table>
        </x-filament-tables::container>
        @endif
    </x-filament::card>

    <x-filament-panels::form>
    {{ $this->form }}
    <x-filament-panels::form.actions
        :actions="$this->getCachedFormActions()"
        :full-width="$this->hasFullWidthFormActions()"
    />
</x-filament-panels::form>

</x-filament-panels::page>
