@php
    $rewardInfos = $getState();
    if (!is_array($rewardInfos) && !($rewardInfos instanceof \Illuminate\Support\Collection)) {
        $rewardInfos = [$rewardInfos];
    }
@endphp
@if(!empty($rewardInfos))
    <div class="reward-content" style="flex-direction: column;">
        @foreach($rewardInfos as $rewardInfo)
            @if(!is_null($rewardInfo))
                <x-reward-info :rewardInfo="$rewardInfo" />
            @endif
        @endforeach
    </div>
@endif
<style>
    /** 報酬情報表示のスタイル */
    .reward-content{
        z-index: 0;
        display: flex;
        align-items: center;
        justify-content: left;
        max-width: 80rem;
        gap: 1rem;
        padding: 1rem;
    }
</style>
