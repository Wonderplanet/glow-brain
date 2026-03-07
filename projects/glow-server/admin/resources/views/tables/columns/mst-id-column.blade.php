@php
    $record = $getRecord();

    $assetPath = $getAssetPath($record);
    $bgPath = $getBgPath($record);
    $hasAsset = \App\Utils\StringUtil::isSpecified($assetPath);

    $detailUrl = $getMstDetailPageUrl($record);
    $hasUrl = !empty($detailUrl);

    $label = '[' . $getMstId($record) . '] ' . $getMstDataName($record);
@endphp

<div>
    <div class="mst-id-column-content text-sm">
        @if ($hasAsset)
            <x-asset-image :assetPath="$assetPath" :bgPath="$bgPath" />
        @endif
        <div>
            @if ($hasUrl)
            <a href="{{ $detailUrl }}" class="link">
                <span class="text-sm">
                {{ $label }}
                </span>
            </a>
            @else
            <span class="text-sm">
                {{ $label }}
            </span>
            @endif
        </div>
    </div>
</div>

<style>
    /** 報酬情報表示のスタイル */
    .mst-id-column-content{
        z-index: 0;
        display: flex;
        align-items: center;
        justify-content: left;
        max-width: 80rem;
        gap: 1rem;
        padding: 1rem;
    }
</style>
