@php
    $mstSeries = $getState();
@endphp
<div >
    <span class="text-sm">
        @if($mstSeries)
            [{{ $mstSeries->id }}] {{ $mstSeries->mst_series_i18n->name }}
        @endif
    </span>
</div>
