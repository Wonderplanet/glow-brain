<?php

declare(strict_types=1);

namespace App\Domain\BoxGacha\Entities;

use App\Domain\Resource\Entities\Rewards\BoxGachaReward;
use Illuminate\Support\Collection;

/**
 * BOXガチャ抽選結果
 *
 * 抽選で得られた報酬とログ用の集計データを保持
 */
class BoxGachaDrawResult
{
    /**
     * @param Collection<int, BoxGachaReward> $rewards 抽選結果の報酬リスト
     * @param Collection<int, BoxGachaDrawPrizeLog> $prizeLogs ログ用の賞品別集計データ
     */
    public function __construct(
        private Collection $rewards,
        private Collection $prizeLogs,
    ) {
    }

    /**
     * @return Collection<int, BoxGachaReward>
     */
    public function getRewards(): Collection
    {
        return $this->rewards;
    }

    /**
     * @return Collection<int, BoxGachaDrawPrizeLog>
     */
    public function getPrizeLogs(): Collection
    {
        return $this->prizeLogs;
    }
}
