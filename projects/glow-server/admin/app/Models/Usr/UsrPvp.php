<?php

namespace App\Models\Usr;

use App\Constants\Database;
use App\Domain\Pvp\Constants\PvpConstant;
use App\Domain\Pvp\Models\UsrPvp as BaseUsrPvp;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsrPvp extends BaseUsrPvp
{
    protected $connection = Database::TIDB_CONNECTION;
    public $timestamps = true;

    public function sys_pvp_seasons(): BelongsTo
    {
        return $this->belongsTo(SysPvpSeason::class, 'sys_pvp_season_id', 'id');
    }

    /**
     * ランキングに登録するスコアを取得する
     * ランキング除外中：-1 それ以外：scoreの値
     * @return int
     */
    public function getScoreForRankingRegistration(): int
    {
        return $this->isExcludedRanking() ? PvpConstant::RANKING_CHEATER_SCORE : $this->score;
    }

    public function usr_user()
    {
        return $this->belongsTo(UsrUser::class, 'usr_user_id', 'id');
    }
}
