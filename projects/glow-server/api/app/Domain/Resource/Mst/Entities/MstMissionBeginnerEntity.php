<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

use App\Domain\Common\Utils\StringUtil;
use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionType;
use App\Domain\Mission\Utils\MissionUtil;
use App\Domain\Resource\Mst\Entities\Contracts\MstMissionEntityInterface;
use App\Domain\Resource\Mst\Entities\Contracts\MstMissionEntityReceiveRewardInterface;

class MstMissionBeginnerEntity implements MstMissionEntityInterface, MstMissionEntityReceiveRewardInterface
{
    public function __construct(
        private string $id,
        private int $releaseKey,
        private string $criterionType,
        private ?string $criterionValue,
        private int $criterionCount,
        private int $unlockDay,
        private ?string $groupKey,
        private int $bonusPoint,
        private string $mstMissionRewardGroupId,
        private int $sortOrder,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getReleaseKey(): int
    {
        return $this->releaseKey;
    }

    public function getProgressGroupKey(): string
    {
        // 進捗グループで進捗を分ける必要がない為、固定値を返す
        return '';
    }

    public function getCriterionType(): string
    {
        return $this->criterionType;
    }

    public function getCriterionValue(): ?string
    {
        return $this->criterionValue;
    }

    public function getCriterionCount(): int
    {
        return $this->criterionCount;
    }

    public function getUnlockCriterionType(): ?string
    {
        if ($this->criterionType === MissionCriterionType::MISSION_BONUS_POINT->value) {
            return null;
        }
        return MissionCriterionType::DAYS_FROM_UNLOCKED_MISSION->value;
    }

    public function getUnlockCriterionValue(): ?string
    {
        // beginnerは開放条件が経過日数のみ
        return null;
    }

    public function getUnlockCriterionCount(): int
    {
        // beginnerは開放条件が経過日数のみ
        return $this->unlockDay;
    }

    public function hasUnlockCriterion(): bool
    {
        if ($this->criterionType === MissionCriterionType::MISSION_BONUS_POINT->value) {
            return false;
        }
        return true;
    }

    public function getGroupKey(): ?string
    {
        if (StringUtil::isNotSpecified($this->groupKey)) {
            return null;
        }
        return $this->groupKey;
    }

    public function getBonusPoint(): int
    {
        return $this->bonusPoint;
    }

    public function getMstMissionRewardGroupId(): string
    {
        return $this->mstMissionRewardGroupId;
    }

    public function getEventCategory(): ?string
    {
        // beginnerでは固定値を返す
        return null;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function getStartAt(): string
    {
        // 開催期間が存在しないので固定値で返す
        return '';
    }

    public function getEndAt(): string
    {
        // 開催期間が存在しないので固定値で返す
        return '';
    }

    public function getCriterionKey(): string
    {
        return MissionUtil::makeCriterionKey($this->criterionType, $this->criterionValue);
    }

    public function getUnlockCriterionKey(): ?string
    {
        if (!$this->hasUnlockCriterion()) {
            return null;
        }
        return MissionUtil::makeCriterionKey(MissionCriterionType::DAYS_FROM_UNLOCKED_MISSION->value, null);
    }

    public function isCompositeMission(): bool
    {
        return MissionUtil::isCompositeMissionCriterionType($this->criterionType);
    }

    public function getMissionType(): MissionType
    {
        return MissionType::BEGINNER;
    }

    public function isBonusPointMission(): bool
    {
        return $this->criterionType === MissionCriterionType::MISSION_BONUS_POINT->value;
    }

    public function getResponseGroupId(): ?string
    {
        return null;
    }
}
