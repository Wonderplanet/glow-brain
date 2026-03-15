<?php

declare(strict_types=1);

namespace App\Domain\Pvp\Entities;

readonly class PvpRankingItem
{
    public function __construct(
        private string $myId,
        private int $rank,
        private string $name,
        private string $mstUnitId,
        private string $mstEmblemId,
        private int $score,
    ) {
    }

    /**
     * @return array<mixed>
     */
    public function formatToResponse(): array
    {
        return [
            'myId' => $this->myId,
            'rank' => $this->rank,
            'name' => $this->name,
            'mstUnitId' => $this->mstUnitId,
            'mstEmblemId' => $this->mstEmblemId,
            'score' => $this->score,
        ];
    }
}
