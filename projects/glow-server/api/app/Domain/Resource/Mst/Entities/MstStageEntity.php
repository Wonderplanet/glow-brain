<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

use App\Domain\Common\Utils\StringUtil;

class MstStageEntity
{
    public function __construct(
        private string $id,
        private string $mstQuestId,
        private int $costStamina,
        private int $exp,
        private int $coin,
        private ?string $mstArtworkFragmentDropGroupId,
        private ?string $prevMstStageId,
        private ?string $autoLapType,
        private int $maxAutoLapCount,
        private int $sortOrder,
        private int $releaseKey,
        private string $startAt,
        private string $endAt,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMstQuestId(): string
    {
        return $this->mstQuestId;
    }

    public function getCostStamina(): int
    {
        return $this->costStamina;
    }

    public function getExp(): int
    {
        return $this->exp;
    }

    public function getCoin(): int
    {
        return $this->coin;
    }

    public function getMstArtworkFragmentDropGroupId(): ?string
    {
        return $this->mstArtworkFragmentDropGroupId;
    }

    public function getPrevMstStageId(): ?string
    {
        return $this->prevMstStageId === '' ? null : $this->prevMstStageId;
    }

    public function getAutoLapType(): ?string
    {
        return $this->autoLapType;
    }

    public function getMaxAutoLapCount(): int
    {
        return $this->maxAutoLapCount;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function getReleaseKey(): int
    {
        return $this->releaseKey;
    }

    public function getStartAt(): string
    {
        return $this->startAt;
    }

    public function getEndAt(): string
    {
        return $this->endAt;
    }

    /**
     * 原画のかけらドロップが設定されているか
     * true: 設定されている, false: 未設定
     * @return bool
     */
    public function hasMstArtworkFragmentDrop(): bool
    {
        return StringUtil::isSpecified($this->mstArtworkFragmentDropGroupId);
    }
}
