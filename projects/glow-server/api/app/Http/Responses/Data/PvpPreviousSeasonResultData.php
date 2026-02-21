<?php

declare(strict_types=1);

namespace App\Http\Responses\Data;

use App\Domain\Pvp\Enums\PvpRankClassType;
use App\Domain\Resource\Entities\Rewards\BaseReward;
use Illuminate\Support\Collection;

class PvpPreviousSeasonResultData
{
    public function __construct(
        public string $rankClassType,
        public int $rankClassLevel,
        public int $score,
        public int $ranking,
        public Collection $rewards,
    ) {
    }

    /**
     * フォーマットされたレスポンスデータを返す
     *
     * @return array<string, mixed>
     */
    public function formatToResponse(): array
    {
        return [
            'pvpRankClassType' => $this->rankClassType,
            'rankClassLevel' => $this->rankClassLevel,
            'score' => $this->score,
            'ranking' => $this->ranking,
            'pvpRewards' => $this->rewards->map(fn($reward) => $reward->formatToResponse())->values()->all(),
        ];
    }
}
