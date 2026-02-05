@php
    $mstUnit = $getState();
    use App\Filament\Pages\MstUnitDetail;
    $url = MstUnitDetail::getUrl(['mstUnitId' => $mstUnit['mst_unit_id']]);
@endphp
<div >
    <a href="{{ $url }}" class="link">
        <span class="text-sm">
            {{ $mstUnit['mst_unit_id'] }}
        </span>
    </a>
</div>
