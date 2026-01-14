<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Domain\Resource\Entities\Rewards\GachaReward;
use Illuminate\Support\Collection;

class TutorialGachaDrawResultData
{
    /**
     * @param Collection<GachaReward> $gachaRewards
     */
    public function __construct(
        public Collection $gachaRewards,
    ) {
    }
}
