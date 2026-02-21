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
            @forelse ($rows as $row)
                <tr>
                    @foreach ($columns as $column)
                    @php
                    $columnValue = $row[$column];
                    @endphp
                    @if (is_array($columnValue))
                        <td class="px-6 py-4">
                            @foreach ($columnValue as $col)
                                <x-reward-info :rewardInfo="$col" />
                            @endforeach
                        </td>
                    @else
                        <td class="px-6 py-4">
                            {{ $columnValue }}
                        </td>
                    @endif
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($columns) }}" class="px-6 py-4 text-center text-gray-500">
                        データがありません
                    </td>
                </tr>
            @endforelse
            </tbody>
    </table>
</x-filament::card>
