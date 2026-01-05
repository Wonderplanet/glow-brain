@php
    $mstArtwork = $getState();

    use App\Filament\Pages\MstArtworkDetail;
    if ($mstArtwork) {
        $url = MstArtworkDetail::getUrl(['mstArtworkId' => $mstArtwork->id]);
    }
@endphp
<div>
    @if ($mstArtwork)
        <a href="{{ $url }}" class="link">
            <span class="text-sm">
                [{{ $mstArtwork->id }}] {{ $mstArtwork?->mst_artwork_i18n?->name }}
            </span>
        </a>
    @endif
</div>
