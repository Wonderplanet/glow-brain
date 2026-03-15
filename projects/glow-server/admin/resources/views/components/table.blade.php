@props([
'title' => '',
'columns' => [],
'rows' => [],
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
    <div style="margin: -1.5rem -1.5rem 1rem; padding: 0.6rem 1.5rem; background-color: {{ $hc['bg'] }}; border-bottom: 1px solid {{ $hc['border'] }}; border-radius: 0.375rem 0.375rem 0 0;">
        <h3 style="font-weight: 700; color: {{ $hc['text'] }}; font-size: 0.875rem;">{{$title}}</h3>
    </div>
    <table class="table">
        <thead>
            <tr>
                @foreach ($columns as $column)
                <th scope="col" class="px-6 py-3">{{ $column }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                @foreach ($columns as $column)
                @php
                $columnValue = $row[$column];
                @endphp
                <td class="px-6 py-4">
                    @if ($columnValue instanceof \App\Entities\RewardInfo)
                        <x-reward-info :rewardInfo="$columnValue" />
                    @else
                        {{ $columnValue }}
                    @endif
                </td>
                @endforeach
            </tr>
            @endforeach
            </tbody>
    </table>
</x-filament::card>
