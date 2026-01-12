@php
    $oprGacha = $getState();

    use App\Filament\Pages\OprGachaDetail;
    if ($oprGacha) {
        $url = OprGachaDetail::getUrl(['oprGachaId' => $oprGacha->id]);
    }
@endphp
<div>
    @if ($oprGacha)
        <a href="{{ $url }}" class="link">
            <span class="text-sm">
                [{{ $oprGacha->id }}] {{ $oprGacha?->opr_gacha_i18n?->name }}
            </span>
        </a>
    @endif
</div>
