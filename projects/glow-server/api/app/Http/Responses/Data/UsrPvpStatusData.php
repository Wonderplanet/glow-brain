<?php

declare(strict_types=1);

namespace App\Http\Responses\Data;

use App\Domain\Pvp\Enums\PvpRankClassType;

class UsrPvpStatusData
{
    public function __construct(
        private int $score,
        private int $maxReceivedScoreReward,
        private PvpRankClassType $pvpRankClassType,
        private int $pvpRankClassLevel,
        private int $dailyRemainingChallengeCount,
        private int $dailyRemainingItemChallengeCount,
    ) {
    }

    public function getScore(): int
    {
        return $this->score;
    }

    public function getMaxReceivedScoreReward(): int
    {
        return $this->maxReceivedScoreReward;
    }

    public function getPvpRankClassType(): PvpRankClassType
    {
        return $this->pvpRankClassType;
    }

    public function getPvpRankClassLevel(): int
    {
        return $this->pvpRankClassLevel;
    }

    public function getDailyRemainingChallengeCount(): int
    {
        return $this->dailyRemainingChallengeCount;
    }

    public function getDailyRemainingItemChallengeCount(): int
    {
        return $this->dailyRemainingItemChallengeCount;
    }

    /**
     * フォーマットされたレスポンスデータを返す
     *
     * @return array<string, mixed>
     */
    public function formatToResponse(): array
    {
        return [
            'score' => $this->getScore(),
            'maxReceivedScoreReward' => $this->getMaxReceivedScoreReward(),
            'pvpRankClassType' => $this->getPvpRankClassType()->value,
            'pvpRankClassLevel' => $this->getPvpRankClassLevel(),
            'dailyRemainingChallengeCount' => $this->getDailyRemainingChallengeCount(),
            'dailyRemainingItemChallengeCount' => $this->getDailyRemainingItemChallengeCount(),
        ];
    }
}
