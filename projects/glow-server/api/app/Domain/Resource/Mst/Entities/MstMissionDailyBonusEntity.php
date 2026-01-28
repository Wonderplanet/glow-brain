<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

use App\Domain\Mission\Enums\MissionDailyBonusType;
use App\Domain\Mission\Enums\MissionType;
use App\Domain\Resource\Mst\Entities\Contracts\MstMissionEntityReceiveRewardInterface;

class MstMissionDailyBonusEntity implements MstMissionEntityReceiveRewardInterface
{
    public function __construct(
        private string $id,
        private int $releaseKey,
        private string $type,
        private int $loginDayCount,
        private string $mstMissionRewardGroupId,
        private int $sortOrder,
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

    public function getType(): string
    {
        return $this->type;
    }

    public function isDailyBonusType(): bool
    {
        return $this->type === MissionDailyBonusType::DAILY_BONUS->value;
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
        return MissionType::DAILY_BONUS;
    }
}
