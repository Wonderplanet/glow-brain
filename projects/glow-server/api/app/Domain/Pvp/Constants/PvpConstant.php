<?php

declare(strict_types=1);

namespace App\Domain\Pvp\Constants;

class PvpConstant
{
    /**
     * mst_pvpsにあるデフォルト設定レコードのID
     *
     * sys_pvp_season_idはシーズンごとに自動生成される。
     * 特定のシーズンのmst_pvpsレコードがない場合は、デフォルト設定を参照する。
     *
     * @var string
     */
    public const DEFAULT_MST_PVP_ID = 'default_pvp';

    /**
     * PVPのランキング表示上限
     */
    public const RANKING_DISPLAY_LIMIT = 200;

    public const RANKING_DISPLAY_DEFAULT_COUNT = 100;

    /**
     * ランキング集合からzrevrangeで取得する際の表示上限と加算してlimitで指定する値
     */
    public const RANKING_FETCH_BUFFER = 50;

    /**
     * ランキングAPIのデータキャッシュ秒数
     */
    public const RANKING_CACHE_TTL_SECONDS = 60 * 5;

    /**
     * チーター集合に登録する際のスコア
     */
    public const RANKING_CHEATER_SCORE = -1;

    // 対戦抽選時のキャッシュ取得するUserIDの上限
    public const MATCHING_CACHE_GET_LIMIT = 100;

    // PVPのバトルスコアの最大値
    public const MAX_BATTLE_SCORE = 999999999999999;
    /**
     * PVPシーズンの非アクティブ期間の制限
     */
    public const INACTIVE_SEASON_LIMIT = 4;

    /**
     * 処理場で過去シーズンとして考慮する開催シーズン数
     */
    public const SEASON_CONSIDER_LIMIT = 3;
}
