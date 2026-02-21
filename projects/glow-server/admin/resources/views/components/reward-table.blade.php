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
            <tr>
                @foreach ($columns as $column)
                @php
                $columnValue = $row[$column];
                @endphp
                @if (is_object($columnValue))
                    <td class="px-6 py-4">
                        <x-reward-info :rewardInfo="$columnValue" />
                    </td>
                @else
                    <td class="px-6 py-4">
                        {{$columnValue}}
                    </td>
                @endif
                @endforeach
            </tr>
            @endforeach
            </tbody>
    </table>
</x-filament::card>
