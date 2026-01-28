<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Http\Responses\Data\UserLevelUpData;
use App\Http\Responses\Data\UsrParameterData;
use Illuminate\Support\Collection;

class TutorialStageEndResultData
{
    /**
     * @param Collection<\App\Domain\Resource\Entities\Rewards\StageFirstClearReward> $stageFirstClearRewards
     * @param Collection<\App\Domain\Item\Models\UsrItemInterface>  $usrItems
     * @param Collection<\App\Domain\Unit\Models\UsrUnitInterface>  $usrUnits
     * @param Collection<\App\Domain\Emblem\Models\UsrEmblemInterface>  $usrEmblems
     */
    public function __construct(
        public string $tutorialStatus,
        public UsrParameterData $usrParameterData,
        public Collection $stageFirstClearRewards,
        public UserLevelUpData $userLevelUpData,
        public Collection $usrItems,
        public Collection $usrUnits,
        public Collection $usrEmblems,
    ) {
    }
}
