<?php

declare(strict_types=1);

namespace App\Domain\AdventBattle\Constants;

class AdventBattleConstant
{
    /**
     * ランキングAPIのデータキャッシュ秒数
     */
    // TODO 秒数はで変わるかもしれない
    public const RANKING_CACHE_TTL_SECONDS = 60 * 5;

    /**
     * ランキング確定後の獲得報酬計算時のためのランキングをチーターのスコアで上書きした集合のキャッシュ秒数
     */
    public const OVERWRITE_RANKING_CACHE_TTL_SECONDS_FOR_REWARD = 60 * 60 * 24 * 30;

    public const RANKING_DISPLAY_LIMIT = 100;

    /**
     * ランキング集合からzrevrangeで取得する際の表示上限と加算してlimitで指定する値
     */
    public const RANKING_FETCH_BUFFER = 50;

    /**
     * チーター集合に登録する際のスコア
     */
    public const RANKING_CHEATER_SCORE = -1;

    /**
     * シーズン報酬がもらえなくなる降臨バトル終了からの経過日数
     */
    public const SEASON_REWARD_LIMIT_DAYS = 30;

    /**
     * ランキングのデフォルト集計時間
     * 基本はMstConfigから取得
     */
    public const DEFAULT_RANKING_AGGREGATE_HOURS = 48;

    /**
     * ランキング報酬内の参加賞指定条件文字列
     */
    public const RANKING_REWARD_PARTICIPATION = 'Participation';
}
