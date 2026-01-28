<?php

declare(strict_types=1);

namespace App\Http\Responses\Data;

readonly class AdventBattleMyRankingData
{
    public function __construct(
        private ?int $rank,
        private ?int $score, // max score
        private ?int $totalScore,
        private bool $isExcludeRanking,
    ) {
    }

    public function getRank(): ?int
    {
        return $this->rank;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function getTotalScore(): ?int
    {
        return $this->totalScore;
    }

    /**
     * @return array<mixed>
     */
    public function formatToResponse(): array
    {
        return [
            'rank' => $this->rank ?? 0,
            'score' => $this->score ?? 0,
            'totalScore' => $this->totalScore ?? 0,
            'isExcludeRanking' => $this->isExcludeRanking,
        ];
    }
}
