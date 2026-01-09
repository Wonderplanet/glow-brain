<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

use App\Domain\Pvp\Enums\PvpRewardCategory;

class MstPvpRewardGroupEntity
{
    public function __construct(
        private string $id,
        private string $rewardCategory,
        private string $conditionValue,
        private string $mstPvpId,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getRewardCategory(): string
    {
        return $this->rewardCategory;
    }

    public function getConditionValue(): string
    {
        return $this->conditionValue;
    }

    public function getMstPvpId(): string
    {
        return $this->mstPvpId;
    }

    public function isRanking(): bool
    {
        return $this->rewardCategory === PvpRewardCategory::RANKING->value;
    }

    public function isRankClass(): bool
    {
        return $this->rewardCategory === PvpRewardCategory::RANK_ClASS->value;
    }

    public function isTotalScore(): bool
    {
        return $this->rewardCategory === PvpRewardCategory::TOTAL_SCORE->value;
    }
}
