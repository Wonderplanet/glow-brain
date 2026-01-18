@php
    $assetPath = $makeAssetPath();
    $bgPath = $makeBgPath();
    $width = $getAssetWidth();
@endphp
<div class="p-2">
    <x-asset-image :assetPath="$assetPath" :bgPath="$bgPath" :imageWidth="$width"/>
</div>
<p class="text-sm font-medium text-gray-950 dark:text-white mt-1">{{ $getAssetKey() }}</p>
