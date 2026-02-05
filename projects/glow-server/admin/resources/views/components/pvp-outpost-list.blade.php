@props([
    'outposts' => '',
    'title' => '',
])

@php
use App\Filament\Pages\MstOutpostDetail;
$mstOutpostDetailUrl = MstOutpostDetail::getUrl();
@endphp

<x-filament::card>
    <h3 class="font-semibold">{{ $title }}</h3>
    <table class="table">
        <tr>
            <th>ゲート強化ID</th>
            <th>ゲート強化名</th>
            <th>レベル</th>
        </tr>
        <tbody>
            @foreach($outposts as $outpost)
            <tr>
                <td>
                    <a href="{{ $mstOutpostDetailUrl }}" class="link">
                        {{ $outpost['mst_outpost_enhancement_id'] }}
                    </a>
                </td>
                <td>{{ $outpost['name'] }}</td>
                <td>{{ $outpost['level'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</x-filament::card>
