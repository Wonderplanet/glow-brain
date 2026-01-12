<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

use App\Domain\Mission\Enums\MissionLimitedTermCategory;
use App\Domain\Mission\Enums\MissionType;
use App\Domain\Mission\Utils\MissionUtil;
use App\Domain\Resource\Mst\Entities\Contracts\MstMissionEntityInterface;
use App\Domain\Resource\Mst\Entities\Contracts\MstMissionEntityReceiveRewardInterface;

class MstMissionLimitedTermEntity implements MstMissionEntityInterface, MstMissionEntityReceiveRewardInterface
{
    public function __construct(
        private string $id,
        private int $releaseKey,
        private string $progressGroupKey,
        private string $criterionType,
        private ?string $criterionValue,
        private int $criterionCount,
        private string $missionCategory,
        private string $mstMissionRewardGroupId,
        private int $sortOrder,
        private string $destinationScene,
        private string $startAt,
        private string $endAt,
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
        return $this->progressGroupKey;
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

    public function getMissionCategory(): string
    {
        return $this->missionCategory;
    }

    public function getUnlockCriterionType(): ?string
    {
        // 期間限定ミッションは開放条件がないので固定値を返す
        return null;
    }

    public function getUnlockCriterionValue(): ?string
    {
        // 期間限定ミッションは開放条件がないので固定値を返す
        return null;
    }

    public function getUnlockCriterionCount(): int
    {
        // 期間限定ミッションは開放条件がないので固定値を返す
        return 0;
    }

    public function getGroupKey(): ?string
    {
        // 期間限定ミッションは「mission_full_complete」がないので固定値を返す
        return null;
    }

    public function getBonusPoint(): int
    {
        // 期間限定ミッションにはボーナスポイントはないので0を返す
        return 0;
    }

    public function getMstMissionRewardGroupId(): string
    {
        return $this->mstMissionRewardGroupId;
    }

    public function getEventCategory(): ?string
    {
        // 期間限定ミッションでは存在しないので固定値を返す
        return null;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function getDestinationScene(): string
    {
        return $this->destinationScene;
    }

    public function getStartAt(): string
    {
        return $this->startAt;
    }

    public function getEndAt(): string
    {
        return $this->endAt;
    }

    public function hasUnlockCriterion(): bool
    {
        // 期間限定ミッションは開放条件がないので固定値を返す
        return false;
    }

    public function getCriterionKey(): string
    {
        return MissionUtil::makeCriterionKey($this->criterionType, $this->criterionValue);
    }

    public function getUnlockCriterionKey(): ?string
    {
        // 期間限定ミッションは開放条件がないので固定値を返す
        return null;
    }

    public function isCompositeMission(): bool
    {
        // 期間限定ミッションでは設定されないミッションなので固定値を返す
        return false;
    }

    public function getMissionType(): MissionType
    {
        return MissionType::LIMITED_TERM;
    }

    public function isAdventBattle(): bool
    {
        return $this->getMissionCategory() === MissionLimitedTermCategory::ADVENT_BATTLE->value;
    }

    public function isBonusPointMission(): bool
    {
        // 期間限定ミッションにはボーナスポイントはないのでfalseを返す
        return false;
    }

    public function getResponseGroupId(): ?string
    {
        return null;
    }
}
