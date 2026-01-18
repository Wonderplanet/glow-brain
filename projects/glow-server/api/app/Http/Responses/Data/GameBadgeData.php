<?php

declare(strict_types=1);

namespace App\Http\Responses\Data;

use Illuminate\Support\Collection;

class GameBadgeData
{
    public function __construct(
        public int $unreceivedMissionRewardCount,
        public int $unreceivedMissionBeginnerRewardCount,
        public int $unopenedMessageCount,
        public Collection $unreceivedMissionEventRewardCounts,
        public int $unreceivedMissionAdventBattleRewardCount,
    ) {
    }
}
