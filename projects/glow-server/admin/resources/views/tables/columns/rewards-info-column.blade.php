@php
    $rewardInfos = $getState();
@endphp
@if($rewardInfos)
    <div>
        @foreach ($rewardInfos as $title => $rewardInfo)
            {{ $title }}
            @foreach ($rewardInfo as $reward)
                <x-reward-info :rewardInfo="$reward" />
            @endforeach
        @endforeach
    </div>
@endif
