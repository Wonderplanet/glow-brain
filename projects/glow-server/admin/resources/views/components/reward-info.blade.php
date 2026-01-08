@props([
    'resourceType' => '',
    'resourceId' => '',
    'name' => '',
    'amount' => '',
    'detailUrl' => '',
    'assetPath' => '',
    'bgPath' => '',
])

@php

$suffix = '';
if ($resourceType === 'IdleCoin') {
    $suffix = '時間';
}

if ($resourceId) {
    $label = "[{$resourceId}]{$name}";
} else {
    $label = $name;
}
if ($amount > 0) {
    $label .= " × {$amount}";
}

$hasUrl = !empty($detailUrl);

@endphp

<div>
    <div class="reward-content text-sm">
        <x-asset-image :assetPath="$assetPath" :bgPath="$bgPath" />
        <div>
            @if ($hasUrl)
                <a href="{{ $detailUrl }}" class="link py-6">
                    {{ $label }}
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
    .reward-content{
        z-index: 0;
        display: flex;
        align-items: center;
        justify-content: left;
        max-width: 80rem;
        gap: 1rem;
        padding: 1rem;
    }
</style>
