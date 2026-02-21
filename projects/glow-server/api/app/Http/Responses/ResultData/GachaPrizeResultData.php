<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Http\Responses\Data\GachaProbabilityData;
use Illuminate\Support\Collection;

class GachaPrizeResultData
{
    /**
     * @param GachaProbabilityData $gachaProbabilityData
     * @param Collection $stepupGachaPrizes
     */
    public function __construct(
        public GachaProbabilityData $gachaProbabilityData,
        public Collection $stepupGachaPrizes,
    ) {
    }
}
