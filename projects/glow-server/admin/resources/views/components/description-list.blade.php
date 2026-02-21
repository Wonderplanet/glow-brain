@props([
'title' => '',
'list' => [],
'compact' => false,
'color' => 'blue',
])
@php
$headerColors = [
    'blue'   => ['bg' => '#eff6ff', 'border' => '#bfdbfe', 'text' => '#1d4ed8'],
    'green'  => ['bg' => '#f0fdf4', 'border' => '#bbf7d0', 'text' => '#15803d'],
    'orange' => ['bg' => '#fff7ed', 'border' => '#fed7aa', 'text' => '#c2410c'],
    'purple' => ['bg' => '#faf5ff', 'border' => '#e9d5ff', 'text' => '#7e22ce'],
    'gray'   => ['bg' => '#f9fafb', 'border' => '#e5e7eb', 'text' => '#374151'],
];
$hc = $headerColors[$color] ?? $headerColors['blue'];
@endphp

<x-filament::card>
    @unless (blank($title))
        <div style="margin: -1.5rem -1.5rem 1rem; padding: 0.6rem 1.5rem; background-color: {{ $hc['bg'] }}; border-bottom: 1px solid {{ $hc['border'] }}; border-radius: 0.375rem 0.375rem 0 0;">
            <h3 style="font-weight: 700; color: {{ $hc['text'] }}; font-size: 0.875rem;">{{ $title }}</h3>
        </div>
    @endif
    <div class="{{ $compact ? 'mt-2' : 'mt-6' }} border-t border-gray-100">
        <dl class="divide-y divide-gray-100">
            @foreach($list as $name => $value)
                <div class="px-4 {{ $compact ? 'py-3' : 'py-6' }} sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <dt class="text-sm font-semibold leading-6 text-gray-900">{{$name}}</dt>
                    @if ($value instanceof \App\Entities\RewardInfo)
                        <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                            <x-reward-info :rewardInfo="$value" />
                        </dd>
                    @elseif (is_string($value) && strpos($value, '.png') !== false)
                        <x-asset-banner-image assetPath="{{$value}}" />
                    @else
                        <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                            {!! nl2br(e($value)) !!}
                        </dd>
                    @endif
                </div>
            @endforeach
        </dl>
    </div>
</x-filament::card>
