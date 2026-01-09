@php
    $mstMission = $getState();
    $url = $link($mstMission->id);
@endphp
<div >
    <a href="{{ $url }}" class="link">
        <span class="text-sm">
            [{{ $mstMission->id }}] {{ $mstMission->mst_mission_i18n?->description ?? '' }}
        </span>
    </a>
</div>
