<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Mst\MstPvpRank;

/**
 * PVPのランクに関するサービス
 */
class PvpRankService
{
    /**
     * ランクポイントからPVPランクを取得する
     * @param int $score
     * @return ?MstPvpRank
     */
    public function getPvpRankByScore(int $score): ?MstPvpRank {
        return MstPvpRank::query()->get()
            ->filter(fn (MstPvpRank $rank) => $rank->required_lower_score <= $score)
            ->sortByDesc('required_lower_score')
            ->first();
    }

}
