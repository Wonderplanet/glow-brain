@php
    $assetPath = $makeAssetPath();
@endphp
<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    @if (!is_null($assetPath))
    <audio controls preload="auto">
        <source src="{{ $assetPath }}" type="audio/wav">
    </audio>
    @endif
</x-dynamic-component>
