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
                        報酬情報がありません
                    </td>
                </tr>
            @endforelse
            </tbody>
    </table>
</x-filament::card>
