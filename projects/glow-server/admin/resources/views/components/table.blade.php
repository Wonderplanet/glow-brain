@props([
'title' => '',
'columns' => [],
'rows' => [],
])

<x-filament::card>
    <div class="mb-4">
        <h3 class="font-bold">{{$title}}</h3>
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
