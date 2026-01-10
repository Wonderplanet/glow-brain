<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Http\Responses\Data\GachaProbabilityData;

class GachaPrizeResultData
{
    public function __construct(
        public GachaProbabilityData $gachaProbabilityData,
    ) {
    }
}
