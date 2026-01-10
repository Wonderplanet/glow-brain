@props([
'mstEvent' => '',
])
@php
use App\Filament\Pages\EventDetail;
$url = EventDetail::getUrl(['mstEventId' => $mstEvent->id]);
@endphp
<div >
    <a href="{{$url}}" class="link">
        <span class="text-sm">
            [{{ $mstEvent->id }}] {{ $mstEvent->mst_event_i18n?->name }}
        </span>
    </a>
</div>
