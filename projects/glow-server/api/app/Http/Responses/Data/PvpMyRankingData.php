<?php

declare(strict_types=1);

namespace App\Http\Responses\Data;

readonly class PvpMyRankingData
{
    public function __construct(
        private ?int $rank,
        private ?int $score,
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

    /**
     * @return array<mixed>
     */
    public function formatToResponse(): array
    {
        return [
            'rank' => $this->rank ?? 0,
            'score' => $this->score ?? 0,
            'isExcludeRanking' => $this->isExcludeRanking,
        ];
    }
}
