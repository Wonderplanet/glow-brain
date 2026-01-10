<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Http\Responses\Data\AdventBattleRankingData;

class AdventBattleRankingResultData
{
    public function __construct(
        public AdventBattleRankingData $adventBattleRankingData,
    ) {
    }
}
