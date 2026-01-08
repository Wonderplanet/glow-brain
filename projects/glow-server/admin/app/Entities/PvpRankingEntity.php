<?php

declare(strict_types=1);

namespace App\Entities;

readonly class PvpRankingEntity
{
    public function __construct(
        private int $rank,
        private string $usrUserId,
        private string $name,
        private int $score,
    ) {
    }

    /**
     * @return array<mixed>
     */
    public function formatToResponse(): array
    {
        return [
            'rank' => $this->rank,
            'usr_user_id' => $this->usrUserId,
            'name' => $this->name,
            'score' => $this->score,
        ];
    }
}
