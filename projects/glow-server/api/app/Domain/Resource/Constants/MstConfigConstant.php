<?php

declare(strict_types=1);

namespace App\Domain\Resource\Constants;

class MstConfigConstant
{
    /** @var string ユニットステータスの指数 */
    public const UNIT_STATUS_EXPONENT = 'UNIT_STATUS_EXPONENT';

    /** @var string 1日の内で広告でスタミナを購入できる最大回数のキー */
    public const MAX_DAILY_BUY_STAMINA_AD_COUNT = 'MAX_DAILY_BUY_STAMINA_AD_COUNT';

    /** @var string スタミナ購入広告視聴のインターバル(分) */
    public const DAILY_BUY_STAMINA_AD_INTERVAL_MINUTES = 'DAILY_BUY_STAMINA_AD_INTERVAL_MINUTES';

    /** @var string 広告視聴で回復するスタミナ最大に対するパーセンテージ */
    public const BUY_STAMINA_AD_PERCENTAGE_OF_MAX_STAMINA = 'BUY_STAMINA_AD_PERCENTAGE_OF_MAX_STAMINA';

    /** @var string ダイヤモンドで回復するスタミナ最大に対するパーセンテージ */
    public const BUY_STAMINA_DIAMOND_PERCENTAGE_OF_MAX_STAMINA = 'BUY_STAMINA_DIAMOND_PERCENTAGE_OF_MAX_STAMINA';

    /** @var string スタミナ購入に必要なダイヤモンド数 */
    public const BUY_STAMINA_DIAMOND_AMOUNT = 'BUY_STAMINA_DIAMOND_AMOUNT';

    /** @var string ユーザー名変更できる間隔の時間 */
    public const USER_NAME_CHANGE_INTERVAL_HOURS = 'USER_NAME_CHANGE_INTERVAL_HOURS';

    /** @var string ステージコンティニューに必要な一次通貨数 */
    public const STAGE_CONTINUE_DIAMOND_AMOUNT = 'STAGE_CONTINUE_DIAMOND_AMOUNT';

    /** @var string デバッグで付与する原画IDのキー(不要になったタイミングで削除) */
    public const DEBUG_GRANT_ARTWORK_IDS = 'DEBUG_GRANT_ARTWORK_IDS';

    /** @var string デバッグでセットするゲートの表示(原画ID)のキー(不要になったタイミングで削除) */
    public const DEBUG_DEFAULT_OUTPOST_ARTWORK_ID = 'DEBUG_DEFAULT_OUTPOST_ARTWORK_ID';

    /** @var string 1スタミナ回復にかかる時間(分) */
    public const RECOVERY_STAMINA_MINUTE = 'RECOVERY_STAMINA_MINUTE';

    /** @var string 所持ユニットのレベルキャップ上限 */
    public const UNIT_LEVEL_CAP = 'UNIT_LEVEL_CAP';

    /** @var string 図鑑新着バッチ消失時の付与無償プリズムの数 */
    public const ENCYCLOPEDIA_FIRST_COLLECTION_REWARD_COUNT = 'ENCYCLOPEDIA_FIRST_COLLECTION_REWARD_COUNT';

    /** @var string 広告表示によるコンティニューの最大回数 */
    public const AD_CONTINUE_MAX_COUNT = 'AD_CONTINUE_MAX_COUNT';


    /**
     * メインクエスト未クリア時の探索報酬として参照するステージID
     * @var string
     */
    public const IDLE_INCENTIVE_INITIAL_REWARD_MST_STAGE_ID = 'IDLE_INCENTIVE_INITIAL_REWARD_MST_STAGE_ID';

    /**
     * 強化クエスト
     */

    /** @var string 強化クエストの報酬となるコイン計算用「N時間分探索コイン × 係数」のNの指定 */
    public const ENHANCE_QUEST_IDLE_COIN_REWARD_HOURS = 'ENHANCE_QUEST_IDLE_COIN_REWARD_HOURS';

    /** @var string 強化クエストのステージに対する通常の挑戦回数 */
    public const ENHANCE_QUEST_CHALLENGE_LIMIT = 'ENHANCE_QUEST_CHALLENGE_LIMIT';

    /** @var string 強化クエストのステージに対する広告視聴による挑戦回数 */
    public const ENHANCE_QUEST_CHALLENGE_AD_LIMIT = 'ENHANCE_QUEST_CHALLENGE_AD_LIMIT';


    /**
     * 降臨バトル
     */

    /** @var string 降臨バトルのランキング集計時間(時間) */
    public const ADVENT_BATTLE_RANKING_AGGREGATE_HOURS = 'ADVENT_BATTLE_RANKING_AGGREGATE_HOURS';

    /** ユニット ランクアップステータス*/
    public const UNIT_RANKUP_COEFFICIENT_PERCENT = 'UNIT_RANKUP_COEFFICIENT_PERCENT';

    /**
     * PVP
     */

    /** @var string PVP挑戦用のアイテムId */
    public const PVP_CHALLENGE_ITEM_ID = 'PVP_CHALLENGE_ITEM_ID';

    /** @var string PVPのランキング表示人数 */
    public const PVP_RANKING_DISPLAY_COUNT = 'PVP_RANKING_DISPLAY_COUNT';

    /**
     * リソース上限管理
     */

    /** @var string ユーザーアイテムの最大所持数 */
    public const USER_ITEM_MAX_AMOUNT = 'USER_ITEM_MAX_AMOUNT';

    /** @var string ユーザーコインの最大所持数 */
    public const USER_COIN_MAX_AMOUNT = 'USER_COIN_MAX_AMOUNT';

    /** @var string ユーザースタミナの最大所持数 */
    public const USER_STAMINA_MAX_AMOUNT = 'USER_STAMINA_MAX_AMOUNT';

    /** @var string ユーザー無償プリズムの最大所持数 */
    public const USER_FREE_DIAMOND_MAX_AMOUNT = 'USER_FREE_DIAMOND_MAX_AMOUNT';

    /**
     * BOXガチャ
     */

    /** @var string BOXガチャの1回のリクエストで抽選できる最大回数 */
    public const BOX_GACHA_MAX_DRAW_COUNT = 'BOX_GACHA_MAX_DRAW_COUNT';

    /**
     * 原画パーティ
     */

    /** @var string 初期から編成されている原画ID */
    public const DEFAULT_ARTWORK_PARTY_ARTWORK_ID = 'DEFAULT_ARTWORK_PARTY_ARTWORK_ID';
}
