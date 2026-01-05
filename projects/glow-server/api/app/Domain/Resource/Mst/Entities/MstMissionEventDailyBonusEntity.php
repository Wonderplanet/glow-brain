<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

use App\Domain\Mission\Enums\MissionType;
use App\Domain\Resource\Mst\Entities\Contracts\MstMissionEntityReceiveRewardInterface;

class MstMissionEventDailyBonusEntity implements MstMissionEntityReceiveRewardInterface
{
    public function __construct(
        private string $id,
        private string $mstMissionEventDailyBonusScheduleId,
        private int $loginDayCount,
        private string $mstMissionRewardGroupId,
        private int $sortOrder,
        private int $releaseKey,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getProgressGroupKey(): string
    {
        // 進捗グループで進捗を分ける必要がない為、固定値を返す
        return '';
    }

    public function getReleaseKey(): int
    {
        return $this->releaseKey;
    }

    public function getMstMissionEventDailyBonusScheduleId(): string
    {
        return $this->mstMissionEventDailyBonusScheduleId;
    }

    public function getLoginDayCount(): int
    {
        return $this->loginDayCount;
    }

    public function getMstMissionRewardGroupId(): string
    {
        return $this->mstMissionRewardGroupId;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function getStartAt(): string
    {
        return '';
    }

    public function getEndAt(): string
    {
        return '';
    }

    // interfaceの実装
    public function getBonusPoint(): int
    {
        // ボーナスポイントはないので0を返す
        return 0;
    }

    public function getMissionType(): MissionType
    {
        return MissionType::EVENT_DAILY_BONUS;
    }
}
