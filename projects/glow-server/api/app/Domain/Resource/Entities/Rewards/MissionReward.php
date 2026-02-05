<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities\Rewards;

use App\Domain\Mission\Enums\MissionType;
use App\Domain\Resource\Dtos\LogTriggerDto;
use App\Domain\Resource\Log\Enums\LogResourceTriggerSource;
use App\Domain\Resource\Mst\Entities\Contracts\MstMissionEntityReceiveRewardInterface;

class MissionReward extends BaseReward
{
    private string $mstMissionId;
    private string $missionType;

    public function __construct(
        string $type,
        ?string $resourceId,
        int $amount,
        MstMissionEntityReceiveRewardInterface $mstMission,
    ) {
        $triggerSource = match ($mstMission->getMissionType()) {
            MissionType::ACHIEVEMENT => LogResourceTriggerSource::MISSION_ACHIEVEMENT_REWARD->value,
            MissionType::BEGINNER => LogResourceTriggerSource::MISSION_BEGINNER_REWARD->value,
            MissionType::DAILY => LogResourceTriggerSource::MISSION_DAILY_REWARD->value,
            MissionType::WEEKLY => LogResourceTriggerSource::MISSION_WEEKLY_REWARD->value,
            MissionType::EVENT => LogResourceTriggerSource::MISSION_EVENT_REWARD->value,
            MissionType::EVENT_DAILY => LogResourceTriggerSource::MISSION_EVENT_DAILY_REWARD->value,
            MissionType::LIMITED_TERM => LogResourceTriggerSource::MISSION_LIMITED_TERM_REWARD->value,
            default => '',
        };

        $this->mstMissionId = $mstMission->getId();
        $this->missionType = $mstMission->getMissionType()->value;

        parent::__construct(
            $type,
            $resourceId,
            $amount,
            new LogTriggerDto(
                $triggerSource,
                $mstMission->getId(),
            ),
        );
    }

    public function formatToResponse(): array
    {
        return [
            'missionType' => $this->missionType,
            'mstMissionId' => $this->mstMissionId,
            'reward' => parent::formatToResponse(),
        ];
    }
}
