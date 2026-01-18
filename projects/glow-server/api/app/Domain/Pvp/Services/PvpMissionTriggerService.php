<?php

declare(strict_types=1);

namespace App\Domain\Pvp\Services;

use App\Domain\Common\Entities\MissionTrigger;
use App\Domain\Mission\Delegators\MissionDelegator;
use App\Domain\Mission\Enums\MissionCriterionType;

class PvpMissionTriggerService
{
    public function __construct(
        // Delegator
        private MissionDelegator $missionDelegator,
    ) {
    }

    public function sendStartTriggers(): void
    {
        $triggers = collect();

        // PVPを X 回挑戦する
        $triggers->push(
            new MissionTrigger(
                MissionCriterionType::PVP_CHALLENGE_COUNT->value,
                null,
                1,
            )
        );

        $this->missionDelegator->addTriggers($triggers);
    }

    public function sendWinTriggers(): void
    {
        $triggers = collect();

        // PVPに X 回勝利する
        $triggers->push(
            new MissionTrigger(
                MissionCriterionType::PVP_WIN_COUNT->value,
                null,
                1,
            )
        );

        $this->missionDelegator->addTriggers($triggers);
    }
}
