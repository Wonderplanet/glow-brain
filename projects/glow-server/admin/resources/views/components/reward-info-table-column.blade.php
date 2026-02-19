@props([
'columns' => [],
'rows' => [],
])

<thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
<tr>
    @foreach ($columns as $column)
    <th scope="col" class="px-6 py-3">{{ $column['label'] }}</th>
    @endforeach
</tr>
</thead>
<tbody>
@foreach ($rows as $row)
<tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
    @foreach ($columns as $column)
    @php
    $columnValue = $row[$column['prop']];
    @endphp
    <td class="px-6 py-4">
        @if (isset($column['component']) === false)
        {{ $columnValue }}
        @elseif ($column['component'] === 'reward-info')
        <x-reward-info :rewardInfo="$columnValue" />
        @endif
    </td>
    @endforeach
</tr>
@endforeach
</tbody>
