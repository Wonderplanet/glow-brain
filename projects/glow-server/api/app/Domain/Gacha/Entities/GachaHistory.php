<?php

declare(strict_types=1);

namespace App\Domain\Gacha\Entities;

use App\Domain\Common\Utils\StringUtil;
use App\Domain\Resource\Entities\Rewards\GachaReward;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * ガシャ1回分(n連)の履歴
 */
class GachaHistory
{
    public function __construct(
        private string $oprGachaId,
        private string $costType,
        private ?string $costId,
        private int $costNum,
        private int $drawCount,
        private CarbonImmutable $playedAt,
        private Collection $results
    ) {
    }

    public function getPlayedAt(): CarbonImmutable
    {
        return $this->playedAt;
    }

    /**
     * @return array<mixed>
     */
    public function formatToResponse(): array
    {
        return [
            'oprGachaId' => $this->oprGachaId,
            'costType' => $this->costType,
            'costId' => $this->costId,
            'costNum' => $this->costNum,
            'drawCount' => $this->drawCount,
            'playedAt' => StringUtil::convertToISO8601($this->playedAt->toDateTimeString()),
            'results' => $this->results->map(function (GachaReward $reward) {
                return [
                    'sortOrder' => $reward->getSortOrder(),
                    'reward' => $reward->formatToResponse(),
                ];
            })->toArray(),
        ];
    }
}
