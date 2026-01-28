<?php

declare(strict_types=1);

namespace App\Http\Responses\Data;

use App\Domain\AdventBattle\Entities\AdventBattleRankingItem;
use Illuminate\Support\Collection;

readonly class AdventBattleRankingData
{
    public function __construct(
        private Collection $adventBattleRankingItems,
        private AdventBattleMyRankingData $adventBattleMyRankingData,
    ) {
    }

    /**
     * @return array<mixed>
     */
    public function formatToResponse(): array
    {
        return [
            'ranking' => $this
                ->adventBattleRankingItems
                ->map(fn (AdventBattleRankingItem $rankingItemData) => $rankingItemData->formatToResponse()),
            'myRanking' => $this->adventBattleMyRankingData->formatToResponse(),
        ];
    }
}
