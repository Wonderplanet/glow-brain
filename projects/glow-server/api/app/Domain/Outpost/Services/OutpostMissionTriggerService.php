<?php

declare(strict_types=1);

namespace App\Domain\Outpost\Services;

use App\Domain\Common\Entities\MissionTrigger;
use App\Domain\Mission\Delegators\MissionDelegator;
use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Outpost\Models\UsrOutpostEnhancementInterface;

class OutpostMissionTriggerService
{
    public function __construct(
        private MissionDelegator $missionDelegator,
    ) {
    }

    public function sendEnhanceTrigger(
        UsrOutpostEnhancementInterface $usrOutpostEnhancement,
        int $beforeLevel,
    ): void {
        $this->missionDelegator->addTrigger(
            new MissionTrigger(
                MissionCriterionType::OUTPOST_ENHANCE_COUNT->value,
                null,
                max(0, $usrOutpostEnhancement->getLevel() - $beforeLevel),
            )
        );

        $this->missionDelegator->addTrigger(
            new MissionTrigger(
                MissionCriterionType::SPECIFIC_OUTPOST_ENHANCE_LEVEL->value,
                $usrOutpostEnhancement->getMstOutpostEnhancementId(),
                $usrOutpostEnhancement->getLevel(),
            )
        );
    }
}
