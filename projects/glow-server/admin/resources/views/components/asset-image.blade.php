@props([
    'assetPath' => '',
    'bgPath' => '',
    'imageWidth' => 50, // デフォルトは元々指定のあった50
])

@php
    $hasBg = \App\Utils\StringUtil::isSpecified($bgPath);
    $styleWidth = "width:".$imageWidth."px;";
@endphp

<div class="stack" style="{{ $styleWidth }}">
    <img class="max-w-sm rounded-lg shadow-2xl" src="{{ $assetPath }}">
    @if ($hasBg)
        <img src="{{ $bgPath }}" class="max-w-sm rounded-lg shadow-2xl"/>
    @endif
</div>
