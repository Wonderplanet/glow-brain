@props([
    'artworks' => '',
    'title' => '',
])

@php
use App\Filament\Pages\MstArtworkDetail;
@endphp

<x-filament::card>
    <h3 class="font-semibold">{{ $title }}</h3>
    <table class="table">
        <tr>
            <th>原画ID</th>
            <th>原画名</th>
            <th>原画画像</th>
        </tr>
        <tbody>
            @foreach($artworks as $artwork)
            <tr>
                <td>
                    <a href="{{ MstArtworkDetail::getUrl(['mstArtworkId' => $artwork['mst_artwork_id']]) }}" class="link">
                        {{ $artwork['mst_artwork_id'] }}
                    </a>
                </td>
                <td>{{ $artwork['name'] }}</td>
                <td><x-asset-image :assetPath="$artwork['asset_path']" :bgPath="$artwork['bg_path']" /></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</x-filament::card>
