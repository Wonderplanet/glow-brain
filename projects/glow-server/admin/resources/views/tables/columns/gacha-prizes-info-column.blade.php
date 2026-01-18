@php
    $gachaPrizeInfos = $getState();
    if ($gachaPrizeInfos && count($gachaPrizeInfos) !== 1) {
        $gachaPrizeInfos = array_chunk($gachaPrizeInfos, 5);
    }
@endphp
<div>
    @if ($gachaPrizeInfos)
        <table class="table">
            <tbody>
            @foreach ($gachaPrizeInfos as $prizeIndex => $gachaPrizeInfo)
                <tr>
                    @if (count($gachaPrizeInfos) === 1)
                        <td>
                            <x-reward-info :rewardInfo="$gachaPrizeInfo" />
                        </td>
                    @else
                        @foreach ($gachaPrizeInfo as $index => $gachaPrize)
                            <td>
                                <x-reward-info :rewardInfo="$gachaPrize" />
                            </td>
                        @endforeach
                    @endif
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif
</div>
