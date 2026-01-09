<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstStageEventSettingEntity
{
    public function __construct(
        private string $id,
        private string $mstStageId,
        private ?string $resetType,
        private ?int $clearableCount,
        private int $adChallengeCount,
        private ?string $backgroundAssetKey,
        private string $startAt,
        private string $endAt,
        private int $releaseKey
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMstStageId(): string
    {
        return $this->mstStageId;
    }

    public function getResetType(): ?string
    {
        return $this->resetType;
    }

    public function getClearableCount(): ?int
    {
        return $this->clearableCount;
    }

    public function getAdChallengeCount(): int
    {
        return $this->adChallengeCount;
    }

    public function getBackgroundAssetKey(): ?string
    {
        return $this->backgroundAssetKey;
    }

    public function getStartAt(): string
    {
        return $this->startAt;
    }

    public function getEndAt(): string
    {
        return $this->endAt;
    }

    public function getReleaseKey(): int
    {
        return $this->releaseKey;
    }

    /**
     * クリア可能回数に制限がある場合にtrue
     * @return bool
     */
    public function hasLimitedClearableCount(): bool
    {
        return $this->hasUnlimitedClearableCount() === false;
    }

    /**
     * クリア可能回数に制限がない場合にtrue
     * @return bool
     */
    public function hasUnlimitedClearableCount(): bool
    {
        return is_null($this->clearableCount);
    }
}
