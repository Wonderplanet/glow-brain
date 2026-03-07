<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstPvpEntity
{
    public function __construct(
        private string $id,
        private ?string $rankingMinPvpRankClass,
        private int $maxDailyChallengeCount,
        private int $maxDailyItemChallengeCount,
        private int $itemChallengeCostAmount,
        private string $mstInGameId,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getRankingMinPvpRankClass(): ?string
    {
        return $this->rankingMinPvpRankClass;
    }

    public function getMaxDailyChallengeCount(): int
    {
        return $this->maxDailyChallengeCount;
    }

    public function getMaxDailyItemChallengeCount(): int
    {
        return $this->maxDailyItemChallengeCount;
    }

    public function getItemChallengeCostAmount(): int
    {
        return $this->itemChallengeCostAmount;
    }

    public function getMstInGameId(): string
    {
        return $this->mstInGameId;
    }
}
