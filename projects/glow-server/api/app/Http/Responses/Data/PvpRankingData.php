<?php

declare(strict_types=1);

namespace App\Http\Responses\Data;

use App\Domain\Pvp\Entities\PvpRankingItem;
use Illuminate\Support\Collection;

readonly class PvpRankingData
{
    public function __construct(
        private Collection $PvpRankingItems,
        private PvpMyRankingData $PvpMyRankingData,
    ) {
    }

    /**
     * @return array<mixed>
     */
    public function formatToResponse(): array
    {
        return [
            'ranking' => $this
                ->PvpRankingItems
                ->map(fn (PvpRankingItem $rankingItemData) => $rankingItemData->formatToResponse()),
            'myRanking' => $this->PvpMyRankingData->formatToResponse(),
        ];
    }
}
