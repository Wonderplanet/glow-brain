<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

use App\Domain\Common\Utils\StringUtil;
use App\Domain\Mission\Enums\MissionEventCategory;
use App\Domain\Mission\Enums\MissionType;
use App\Domain\Mission\Utils\MissionUtil;
use App\Domain\Resource\Mst\Entities\Contracts\MstMissionEntityInterface;
use App\Domain\Resource\Mst\Entities\Contracts\MstMissionEntityReceiveRewardInterface;

class MstMissionEventEntity implements MstMissionEntityInterface, MstMissionEntityReceiveRewardInterface
{
    public function __construct(
        private string $id,
        private int $releaseKey,
        private string $mstEventId,
        private string $criterionType,
        private ?string $criterionValue,
        private int $criterionCount,
        private ?string $unlockCriterionType,
        private ?string $unlockCriterionValue,
        private int $unlockCriterionCount,
        private ?string $groupKey,
        private string $mstMissionRewardGroupId,
        private ?string $eventCategory,
        private int $sortOrder,
        private string $destinationScene,
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

    public function getMstEventId(): string
    {
        return $this->mstEventId;
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
        return $this->unlockCriterionType;
    }

    public function getUnlockCriterionValue(): ?string
    {
        return $this->unlockCriterionValue;
    }

    public function getUnlockCriterionCount(): int
    {
        return $this->unlockCriterionCount;
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
        // イベントミッションにはボーナスポイントはないので0を返す
        return 0;
    }

    public function getMstMissionRewardGroupId(): string
    {
        return $this->mstMissionRewardGroupId;
    }

    public function getEventCategory(): ?string
    {
        return $this->eventCategory;
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
        // 開催はイベントマスターを参照する
        return '';
    }

    public function getEndAt(): string
    {
        // 開催はイベントマスターを参照する
        return '';
    }

    public function hasUnlockCriterion(): bool
    {
        return StringUtil::isNotSpecified($this->unlockCriterionType) === false;
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
        return MissionUtil::makeCriterionKey($this->unlockCriterionType, $this->unlockCriterionValue);
    }

    public function isCompositeMission(): bool
    {
        return MissionUtil::isCompositeMissionCriterionType($this->criterionType);
    }

    public function isAdventBattle(): bool
    {
        return $this->getEventCategory() === MissionEventCategory::ADVENT_BATTLE->value;
    }

    public function getMissionType(): MissionType
    {
        return MissionType::EVENT;
    }

    public function isBonusPointMission(): bool
    {
        // イベントミッションにはボーナスポイントはないのでfalseを返す
        return false;
    }

    public function getResponseGroupId(): ?string
    {
        return $this->mstEventId;
    }
}
