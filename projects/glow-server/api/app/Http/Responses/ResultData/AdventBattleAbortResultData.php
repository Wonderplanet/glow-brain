<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Http\Responses\Data\AdventBattleMyRankingData;

class AdventBattleAbortResultData
{
    public function __construct(
        public readonly int $allUserTotalScore,
    ) {
    }
}
