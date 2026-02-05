<?php

declare(strict_types=1);

namespace App\Http\Responses\Data;

use Illuminate\Support\Collection;

class GameBadgeData
{
    /**
     * @param int $unreceivedMissionRewardCount
     * @param int $unreceivedMissionBeginnerRewardCount
     * @param int $unopenedMessageCount
     * @param Collection<string, int> $unreceivedMissionEventRewardCounts
     * @param int $unreceivedMissionAdventBattleRewardCount
     * @param Collection<string, int> $unreceivedMissionArtworkPanelRewardCounts key: mstArtworkPanelMissionId, value: count
     */
    public function __construct(
        public int $unreceivedMissionRewardCount,
        public int $unreceivedMissionBeginnerRewardCount,
        public int $unopenedMessageCount,
        public Collection $unreceivedMissionEventRewardCounts,
        public int $unreceivedMissionAdventBattleRewardCount,
        public Collection $unreceivedMissionArtworkPanelRewardCounts,
    ) {
    }
}
