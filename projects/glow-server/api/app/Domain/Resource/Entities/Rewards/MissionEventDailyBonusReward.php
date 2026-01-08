<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities\Rewards;

use App\Domain\Resource\Dtos\LogTriggerDto;
use App\Domain\Resource\Log\Enums\LogResourceTriggerSource;
use App\Domain\Resource\Mst\Entities\MstMissionEventDailyBonusEntity;

class MissionEventDailyBonusReward extends BaseReward
{
    private string $mstMissionEventDailyBonusScheduleId;
    private int $loginDayCount;

    public function __construct(
        string $type,
        ?string $resourceId,
        int $amount,
        MstMissionEventDailyBonusEntity $mstMissionEventDailyBonusEntity,
    ) {
        parent::__construct(
            $type,
            $resourceId,
            $amount,
            new LogTriggerDto(
                LogResourceTriggerSource::MISSION_EVENT_DAILY_BONUS_REWARD->value,
                $mstMissionEventDailyBonusEntity->getId(),
            ),
        );

        // 1行当たりの文字数が多すぎエラー(phpcs)が出るので、変数に格納しています
        $mstScheduleId = $mstMissionEventDailyBonusEntity->getMstMissionEventDailyBonusScheduleId();
        $this->mstMissionEventDailyBonusScheduleId = $mstScheduleId;

        $this->loginDayCount = $mstMissionEventDailyBonusEntity->getLoginDayCount();
    }

    public function getMstMissionEventDailyBonusScheduleId(): string
    {
        return $this->mstMissionEventDailyBonusScheduleId;
    }

    public function getLoginDayCount(): int
    {
        return $this->loginDayCount;
    }
}
