<?php

declare(strict_types=1);

namespace App\Http\Responses\Data;

readonly class AdventBattleResultData
{
    public function __construct(
        private string $mstAdventBattleId,
        private AdventBattleMyRankingData $adventBattleMyRankingData,
        private ?int $totalDamage
    ) {
    }

    public function getAdventBattleMyRankingData(): AdventBattleMyRankingData
    {
        return $this->adventBattleMyRankingData;
    }

    public function getTotalDamage(): ?int
    {
        return $this->totalDamage;
    }

    /**
     * @return array<mixed>
     */
    public function formatToResponse(): array
    {
        return [
            'mstAdventBattleId' => $this->mstAdventBattleId,
            'myRanking' => $this->adventBattleMyRankingData->formatToResponse(),
            'totalDamage' => $this->totalDamage,
        ];
    }
}
