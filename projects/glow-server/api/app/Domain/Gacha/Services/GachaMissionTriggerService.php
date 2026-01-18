<?php

declare(strict_types=1);

namespace App\Domain\Gacha\Services;

use App\Domain\Common\Entities\MissionTrigger;
use App\Domain\Mission\Delegators\MissionDelegator;
use App\Domain\Mission\Enums\MissionCriterionType;

class GachaMissionTriggerService
{
    public function __construct(
        private MissionDelegator $missionDelegator,
    ) {
    }

    /**
     * ガシャを引いた際のミッショントリガーを送信する
     */
    public function sendDrawTrigger(
        string $oprGachaId,
        int $drawCount,
    ): void {
        $this->missionDelegator->addTrigger(
            new MissionTrigger(
                MissionCriterionType::SPECIFIC_GACHA_DRAW_COUNT->value,
                $oprGachaId,
                $drawCount,
            )
        );

        $this->missionDelegator->addTrigger(
            new MissionTrigger(
                MissionCriterionType::GACHA_DRAW_COUNT->value,
                null,
                $drawCount,
            )
        );
    }
}
