<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Http\Responses\Data\PvpRankingData;

class PvpRankingResultData
{
    public function __construct(
        public PvpRankingData $pvpRankingData,
    ) {
    }
}
