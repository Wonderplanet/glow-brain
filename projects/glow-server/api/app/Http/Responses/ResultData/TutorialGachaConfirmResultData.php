<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Domain\Item\Models\UsrItemInterface;
use App\Domain\Resource\Entities\Rewards\GachaReward;
use App\Domain\Unit\Models\UsrUnitInterface;
use App\Http\Responses\Data\UsrParameterData;
use Illuminate\Support\Collection;

class TutorialGachaConfirmResultData
{
    /**
     * @param string $tutorialStatus
     * @param Collection<GachaReward> $gachaRewards
     * @param Collection<UsrUnitInterface> $usrUnits
     * @param Collection<UsrItemInterface> $usrItems
     * @param \App\Http\Responses\Data\UsrParameterData $usrParameterData
     */
    public function __construct(
        public string $tutorialStatus,
        public Collection $gachaRewards,
        public Collection $usrUnits,
        public Collection $usrItems,
        public UsrParameterData $usrParameterData,
    ) {
    }
}
