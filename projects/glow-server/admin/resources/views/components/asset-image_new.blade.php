@props([
'assetPath' => '',
'assetKey' => '',
'rarity' => '',
])
@if ($assetKey)
    <div class="stack" style="width:50px;">
        <img class="max-w-sm rounded-lg shadow-2xl" src="{{ $assetPath . $assetKey . ".png" }}">
        <img src="{{ asset($rarity) . ".png" }}" class="max-w-sm rounded-lg shadow-2xl"/>
    </div>
@endif
