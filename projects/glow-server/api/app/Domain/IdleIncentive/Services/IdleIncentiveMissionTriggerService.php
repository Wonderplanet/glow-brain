<?php

declare(strict_types=1);

namespace App\Domain\IdleIncentive\Services;

use App\Domain\Common\Entities\MissionTrigger;
use App\Domain\Mission\Delegators\MissionDelegator;
use App\Domain\Mission\Enums\MissionCriterionType;

class IdleIncentiveMissionTriggerService
{
    public function __construct(
        private MissionDelegator $missionDelegator,
    ) {
    }

    public function sendQuickReceiveTrigger(): void
    {
        $this->missionDelegator->addTrigger(
            new MissionTrigger(
                MissionCriterionType::IDLE_INCENTIVE_QUICK_COUNT->value,
                null,
                1,
            )
        );
    }

    public function sendReceiveTrigger(): void
    {
        $this->missionDelegator->addTrigger(
            new MissionTrigger(
                MissionCriterionType::IDLE_INCENTIVE_COUNT->value,
                null,
                1,
            )
        );
    }
}
