@php
    $mstMission = $getState();
    $url = $link($mstMission->id);
@endphp
<div>
    <a href="{{ $url }}" class="link">
        <span class="text-sm">
            [{{ $mstMission->id }}] {{ $mstMission->type_label }} {{ $mstMission->login_day_count }}日
        </span>
    </a>
</div>
