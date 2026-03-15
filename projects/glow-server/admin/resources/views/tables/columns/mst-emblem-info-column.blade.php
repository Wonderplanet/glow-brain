@php
    $mstEmblem = $getState();

    use App\Filament\Pages\EmblemDetail;
    if ($mstEmblem->id) {
        $url = EmblemDetail::getUrl(['mstEmblemId' => $mstEmblem->id]);
    }
@endphp
<div>
    @if ($mstEmblem)
        <a href="{{ $url }}" class="link">
            @if ($mstEmblem->id)
                <span class="text-sm">
                [{{ $mstEmblem->id }}] {{ $mstEmblem->mst_emblem_i18n->name ?? '' }}
                </span>
            @endif
        </a>
    @endif
</div>
