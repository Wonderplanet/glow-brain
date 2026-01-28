@php
    $mstEvent = $getState();
@endphp
<div >
    <span class="text-sm">
        @if($mstEvent)
            [{{ $mstEvent->id }}] {{ $mstEvent->mst_event_i18n->name }}
        @endif
    </span>
</div>
