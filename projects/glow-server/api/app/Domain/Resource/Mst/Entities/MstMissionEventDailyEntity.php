<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

use App\Domain\Common\Utils\StringUtil;
use App\Domain\Mission\Enums\MissionType;
use App\Domain\Mission\Utils\MissionUtil;
use App\Domain\Resource\Mst\Entities\Contracts\MstMissionEntityInterface;
use App\Domain\Resource\Mst\Entities\Contracts\MstMissionEntityReceiveRewardInterface;

class MstMissionEventDailyEntity implements MstMissionEntityInterface, MstMissionEntityReceiveRewardInterface
{
    public function __construct(
        private string $id,
        private int $releaseKey,
        private string $mstEventId,
        private string $criterionType,
        private ?string $criterionValue,
        private int $criterionCount,
        private ?string $groupKey,
        private string $mstMissionRewardGroupId,
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

    public function getGroupKey(): ?string
    {
        if (StringUtil::isNotSpecified($this->groupKey)) {
            return null;
        }
        return $this->groupKey;
    }

    public function getBonusPoint(): int
    {
        // イベントデイリーミッションにはボーナスポイントはないので0を返す
        return 0;
    }

    public function getMstMissionRewardGroupId(): string
    {
        return $this->mstMissionRewardGroupId;
    }

    public function getEventCategory(): ?string
    {
        // イベントデイリーミッションでは固定値を返す
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
        // 開催はイベントマスターを参照する
        return '';
    }

    public function getEndAt(): string
    {
        // 開催はイベントマスターを参照する
        return '';
    }

    public function getUnlockCriterionType(): ?string
    {
        // イベントデイリーミッションは開放条件がないので固定値を返す
        return null;
    }

    public function getUnlockCriterionValue(): ?string
    {
        // イベントデイリーミッションは開放条件がないので固定値を返す
        return null;
    }

    public function getUnlockCriterionCount(): int
    {
        // イベントデイリーミッションは開放条件がないので固定値を返す
        return 0;
    }

    public function hasUnlockCriterion(): bool
    {
        // イベントデイリーミッションは開放条件がないので固定値を返す
        return false;
    }

    public function getCriterionKey(): string
    {
        return MissionUtil::makeCriterionKey($this->criterionType, $this->criterionValue);
    }

    public function getUnlockCriterionKey(): ?string
    {
        // イベントデイリーミッションは開放条件がないので固定値を返す
        return null;
    }

    public function isCompositeMission(): bool
    {
        return MissionUtil::isCompositeMissionCriterionType($this->criterionType);
    }

    public function getMissionType(): MissionType
    {
        return MissionType::EVENT_DAILY;
    }

    public function isBonusPointMission(): bool
    {
        // イベントデイリーミッションにはボーナスポイントはないのでfalseを返す
        return false;
    }

    public function getResponseGroupId(): ?string
    {
        return $this->mstEventId;
    }
}
