@php
    $mstStage = $getState();

    use App\Filament\Pages\StageDetail;
    if ($mstStage) {
        $url = StageDetail::getUrl(['stageId' => $mstStage->id]);
    }
@endphp
<div>
    @if ($mstStage)
        <a href="{{ $url }}" class="link">
            <span class="text-sm">
                [{{ $mstStage->id }}] {{ $mstStage?->mst_stage_i18n?->name }}
            </span>
        </a>
    @endif
</div>
