<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities\Rewards;

use App\Domain\Resource\Dtos\LogTriggerDto;
use App\Domain\Resource\Log\Enums\LogResourceTriggerSource;
use App\Domain\Resource\Mst\Entities\MstMissionDailyBonusEntity;

class MissionDailyBonusReward extends BaseReward
{
    private string $dailyBonusType;
    private int $loginDayCount;

    public function __construct(
        string $type,
        ?string $resourceId,
        int $amount,
        MstMissionDailyBonusEntity $mstMissionDailyBonusEntity,
    ) {
        parent::__construct(
            $type,
            $resourceId,
            $amount,
            new LogTriggerDto(
                LogResourceTriggerSource::MISSION_DAILY_BONUS_REWARD->value,
                $mstMissionDailyBonusEntity->getId(),
            ),
        );

        $this->dailyBonusType = $mstMissionDailyBonusEntity->getType();
        $this->loginDayCount = $mstMissionDailyBonusEntity->getLoginDayCount();
    }

    public function getDailyBonusType(): string
    {
        return $this->dailyBonusType;
    }

    public function getLoginDayCount(): int
    {
        return $this->loginDayCount;
    }
}
