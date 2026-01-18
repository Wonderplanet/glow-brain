<?php

declare(strict_types=1);

namespace App\Domain\Item\Services;

use App\Domain\Common\Entities\MissionTrigger;
use App\Domain\Mission\Delegators\MissionDelegator;
use App\Domain\Mission\Enums\MissionCriterionType;

class ItemMissionTriggerService
{
    public function __construct(
        private MissionDelegator $missionDelegator,
    ) {
    }

    public function sendItemCollectTrigger(
        string $mstItemId,
        int $collectedCount,
    ): void {
        $this->missionDelegator->addTrigger(
            new MissionTrigger(
                MissionCriterionType::SPECIFIC_ITEM_COLLECT->value,
                $mstItemId,
                $collectedCount,
            )
        );
    }
}
