@props([
    'units' => '',
    'title' => '',
])

@php
use App\Filament\Pages\MstUnitDetail;
@endphp

<x-filament::card>
    <h3 class="font-semibold">{{ $title }}</h3>
    <table class="table">
        <tr>
            <th>キャラID</th>
            <th>キャラ名称</th>
            <th>キャラ画像</th>
            <th>レベル</th>
            <th>ランク</th>
            <th>グレードレベル</th>
        </tr>
        <tbody>
            @foreach($units as $unit)
            <tr>
                <td>
                    <a href="{{ MstUnitDetail::getUrl(['mstUnitId' => $unit['mst_unit_id']]) }}" class="link">
                        {{ $unit['mst_unit_id'] }}
                    </a>
                </td>
                <td>{{ $unit['name'] }}</td>
                <td><x-asset-image :assetPath="$unit['asset_path']" :bgPath="$unit['bg_path']" /></td>
                <td>{{ $unit['level'] }}</td>
                <td>{{ $unit['rank'] }}</td>
                <td>{{ $unit['grade_level'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</x-filament::card>
