<?php

declare(strict_types=1);

namespace App\Http\Responses\Data;

readonly class AdventBattleRaidTotalScoreData
{
    public function __construct(
        private string $mstAdventBattleId,
        private int $totalDamage,
    ) {
    }

    /**
     * @return array<mixed>
     */
    public function formatToResponse(): array
    {
        return [
            'mstAdventBattleId' => $this->mstAdventBattleId,
            'totalDamage' => $this->totalDamage,
        ];
    }
}
