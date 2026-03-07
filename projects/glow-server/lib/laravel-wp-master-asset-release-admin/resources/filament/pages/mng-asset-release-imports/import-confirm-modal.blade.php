<div>
    @if (isset($this->diffSizeAndroid))
        <p class="text-2xl">Android</p>
        <div class="border" style="margin-bottom: 3rem; border-width: 2px;">
            <p class="font-bold" style="margin-bottom: 0.5rem;">
                @if ($this->androidAssetVersionInfoOfFromEnvironment['status'] === 3)
                    <span style='color: gray'>配信終了</span>
                @elseif ($this->androidAssetVersionInfoOfFromEnvironment['status'] === 2)
                    <span style='color: darkolivegreen'>配信準備中</span>
                @elseif ($this->androidAssetVersionInfoOfFromEnvironment['status'] === 1)
                    <span style='color: deeppink'>配信中</span>
                @endif
                {{ $this->androidAssetVersionInfoOfFromEnvironment['release_key'] }}:{{ $this->androidAssetVersionInfoOfFromEnvironment['description'] }}
            </p>
            <p class="flex" style="margin-bottom: 0.5rem;">
                <x-filament::badge color="success">新規:{{$this->androidDiffCount['newCount']}}件</x-filament::badge>
                <x-filament::badge color="danger" style="margin-left: 0.5rem;">削除:{{$this->androidDiffCount['deleteCount']}}件</x-filament::badge>
                <x-filament::badge color="primary" style="margin-left: 0.5rem;">変更:{{$this->androidDiffCount['changeCount']}}件</x-filament::badge>
            </p>
            <x-filament-tables::table style="margin-bottom: 0.5rem;">
                <x-slot name="header">
                    <x-filament-tables::header-cell style="width:15rem"></x-filament-tables::header-cell>
                    <x-filament-tables::header-cell style="width:25rem">変更前</x-filament-tables::header-cell>
                    <x-filament-tables::header-cell class="w-auto"></x-filament-tables::header-cell>
                    <x-filament-tables::header-cell class="w-auto">変更後</x-filament-tables::header-cell>
                </x-slot>
                <div class="px-4 py-1 items-center">
                    <x-filament-tables::row :recordAction="true">
                        <x-filament-tables::cell>Gitリビジョン</x-filament-tables::cell>
                        <x-filament-tables::cell>
                            @if ($this->releaseKeyAndroid !== $this->androidAssetVersionInfoOfEnvironment['release_key'])
                            (設定なし)
                            @else
                            {{ $this->androidAssetVersionInfoOfEnvironment['git_revision'] }}
                            @endif
                        </x-filament-tables::cell>
                        <x-filament-tables::cell>→</x-filament-tables::cell>
                        <x-filament-tables::cell style="color: deeppink">{{ $this->androidAssetVersionInfoOfFromEnvironment['git_revision'] }}</x-filament-tables::cell>
                    </x-filament-tables::row>
                </div>
                <div class="px-4 py-1 items-center">
                    <x-filament-tables::row :recordAction="true">
                        <x-filament-tables::cell>CatalogHash</x-filament-tables::cell>
                        <x-filament-tables::cell>
                            @if ($this->releaseKeyAndroid !== $this->androidAssetVersionInfoOfEnvironment['release_key'])
                            (設定なし)
                            @else
                            {{ $this->androidAssetVersionInfoOfEnvironment['catalog_hash'] }}
                            @endif
                        </x-filament-tables::cell>
                        <x-filament-tables::cell>→</x-filament-tables::cell>
                        <x-filament-tables::cell style="color: deeppink">{{ $this->androidAssetVersionInfoOfFromEnvironment['catalog_hash'] }}</x-filament-tables::cell>
                    </x-filament-tables::row>
                </div>
                <div class="px-4 py-1 items-center">
                    <x-filament-tables::row :recordAction="true">
                        <x-filament-tables::cell>TotalBytes</x-filament-tables::cell>
                        <x-filament-tables::cell>
                            @if ($this->releaseKeyAndroid !== $this->androidAssetVersionInfoOfEnvironment['release_key'])
                            (設定なし)
                            @else
                            {{ $this->androidAssetTotalBytesEnvironment }}
                            @endif
                        </x-filament-tables::cell>
                        <x-filament-tables::cell>→</x-filament-tables::cell>
                        <x-filament-tables::cell style="color: deeppink">{{ $this->androidAssetTotalBytesFromEnvironment }}</x-filament-tables::cell>
                    </x-filament-tables::row>
                </div>
            </x-filament-tables::table>
        </div>
    @endif
    @if (isset($this->diffSizeIos))
        <p class="text-2xl">iOS</p>
        <div class="border" style="margin-bottom: 3rem; border-width: 2px;">
            <p class="font-bold" style="margin-bottom: 0.5rem;">
                @if ($this->iosAssetVersionInfoOfFromEnvironment['status'] === 3)
                <span style='color: gray'>配信終了</span>
                @elseif ($this->iosAssetVersionInfoOfFromEnvironment['status'] === 2)
                <span style='color: darkolivegreen'>配信準備中</span>
                @elseif ($this->iosAssetVersionInfoOfFromEnvironment['status'] === 1)
                <span style='color: deeppink'>配信中</span>
                @endif
                {{ $this->iosAssetVersionInfoOfFromEnvironment['release_key'] }}:{{ $this->iosAssetVersionInfoOfFromEnvironment['description'] }}
            </p>
            <p class="flex" style="margin-bottom: 0.5rem;">
                <x-filament::badge color="success">新規:{{$this->iosDiffCount['newCount']}}件</x-filament::badge>
                <x-filament::badge color="danger" style="margin-left: 0.5rem;">削除:{{$this->iosDiffCount['deleteCount']}}件</x-filament::badge>
                <x-filament::badge color="primary" style="margin-left: 0.5rem;">変更:{{$this->iosDiffCount['changeCount']}}件</x-filament::badge>
            </p>
            <x-filament-tables::table style="margin-bottom: 0.5rem;">
                <x-slot name="header">
                    <x-filament-tables::header-cell style="width:15rem"></x-filament-tables::header-cell>
                    <x-filament-tables::header-cell style="width:25rem">変更前</x-filament-tables::header-cell>
                    <x-filament-tables::header-cell class="w-auto"></x-filament-tables::header-cell>
                    <x-filament-tables::header-cell class="w-auto">変更後</x-filament-tables::header-cell>
                </x-slot>
                <div class="px-4 py-1 items-center">
                    <x-filament-tables::row :recordAction="true">
                        <x-filament-tables::cell>Gitリビジョン</x-filament-tables::cell>
                        <x-filament-tables::cell>
                            @if ($this->releaseKeyIos !== $this->iosAssetVersionInfoOfEnvironment['release_key'])
                            (設定なし)
                            @else
                            {{ $this->iosAssetVersionInfoOfEnvironment['git_revision'] }}
                            @endif
                        </x-filament-tables::cell>
                        <x-filament-tables::cell>→</x-filament-tables::cell>
                        <x-filament-tables::cell style="color: deeppink">{{ $this->iosAssetVersionInfoOfFromEnvironment['git_revision'] }}</x-filament-tables::cell>
                    </x-filament-tables::row>
                </div>
                <div class="px-4 py-1 items-center">
                    <x-filament-tables::row :recordAction="true">
                        <x-filament-tables::cell>CatalogHash</x-filament-tables::cell>
                        <x-filament-tables::cell>
                            @if ($this->releaseKeyIos !== $this->iosAssetVersionInfoOfEnvironment['release_key'])
                            (設定なし)
                            @else
                            {{ $this->iosAssetVersionInfoOfEnvironment['catalog_hash'] }}
                            @endif
                        </x-filament-tables::cell>
                        <x-filament-tables::cell>→</x-filament-tables::cell>
                        <x-filament-tables::cell style="color: deeppink">{{ $this->iosAssetVersionInfoOfFromEnvironment['catalog_hash'] }}</x-filament-tables::cell>
                    </x-filament-tables::row>
                </div>
                <div class="px-4 py-1 items-center">
                    <x-filament-tables::row :recordAction="true">
                        <x-filament-tables::cell>TotalBytes</x-filament-tables::cell>
                        <x-filament-tables::cell>
                            @if ($this->releaseKeyIos !== $this->iosAssetVersionInfoOfEnvironment['release_key'])
                            (設定なし)
                            @else
                            {{ $this->iosAssetTotalBytesEnvironment }}
                            @endif
                        </x-filament-tables::cell>
                        <x-filament-tables::cell>→</x-filament-tables::cell>
                        <x-filament-tables::cell style="color: deeppink">{{ $this->iosAssetTotalBytesFromEnvironment }}</x-filament-tables::cell>
                    </x-filament-tables::row>
                </div>
            </x-filament-tables::table>
        </div>
    @endif
</div>
