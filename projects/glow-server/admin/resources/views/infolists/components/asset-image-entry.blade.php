@php
    $assetPath = $makeAssetPath();
    $bgPath = $makeBgPath();
    $width = $makeWidth();
    $assetKey = $getAssetKey();
@endphp
<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    <x-asset-image :assetPath="$assetPath" :bgPath="$bgPath" :imageWidth="$width"/>
</x-dynamic-component>
@if (!is_null($assetKey))
<p class="text-sm font-medium text-gray-950 dark:text-white mt-1">{{ $assetKey }}</p>
@endif