<?php

declare(strict_types=1);

namespace App\Domain\Resource\Log\Enums;

/**
 * リソース量変動ログの変動経緯情報の概要情報となる文字列を管理するEnum
 */
enum LogResourceTriggerSource: string
{
    /**
     * 報酬獲得経緯
     */

    // アイテム

    // かけらボックスから変換して得られた報酬
    case ITEM_REWARD = 'ItemReward';

    // ミッション

    // アチーブメントミッションの達成報酬
    case MISSION_ACHIEVEMENT_REWARD = 'MissionAchievementReward';

    // 初心者ミッションの達成報酬
    case MISSION_BEGINNER_REWARD = 'MissionBeginnerReward';

    // デイリーミッションの達成報酬
    case MISSION_DAILY_REWARD = 'MissionDailyReward';

    // ウィークリーミッションの達成報酬
    case MISSION_WEEKLY_REWARD = 'MissionWeeklyReward';

    // イベントミッションの達成報酬
    case MISSION_EVENT_REWARD = 'MissionEventReward';

    // イベントデイリーミッションの達成報酬
    case MISSION_EVENT_DAILY_REWARD = 'MissionEventDailyReward';

    // 期間限定ミッションの達成報酬
    case MISSION_LIMITED_TERM_REWARD = 'MissionLimitedTermReward';

    // デイリーボーナスの達成報酬
    case MISSION_DAILY_BONUS_REWARD = 'MissionDailyBonusReward';

    // イベントデイリーボーナスの達成報酬
    case MISSION_EVENT_DAILY_BONUS_REWARD = 'MissionEventDailyBonusReward';

    // カムバックボーナスの達成報酬
    case COMEBACK_BONUS_REWARD = 'ComebackBonusReward';

    // メッセージ

    // 運営配布メッセージからの配布物報酬
    case MESSAGE_REWARD = 'MessageReward';

    // システムメッセージ(ユーザー未受取報酬など)の報酬
    case SYSTEM_MESSAGE_REWARD = 'SystemMessageReward';

    // ショップ

    // ショップアイテム交換物
    case SHOP_ITEM_REWARD = 'ShopItemReward';

    // ショップパス購入物
    case SHOP_PASS_REWARD = 'ShopPassReward';

    // ショップダイアモンド購入物
    case SHOP_DIAMOND_REWARD = 'ShopDiamondReward';

    // ショップパックで受け取った報酬
    case SHOP_PACK_CONTENT_REWARD = 'ShopPackContentReward';

    // ショップで商品購入(リアルマネーを使用)して受け取った購入物
    case SHOP_PURCHASED_REWARD = 'ShopPurchasedReward';

    // 交換所

    // 交換所で消費したコスト
    case EXCHANGE_TRADE_COST = 'ExchangeTradeCost';

    // 交換所で交換して得られた報酬
    case EXCHANGE_TRADE_REWARD = 'ExchangeTradeReward';

    // ガシャ

    // ガシャ排出物
    case GACHA_REWARD = 'GachaReward';

    // ステージ

    // ステージクリア定常報酬
    case STAGE_ALWAYS_CLEAR_REWARD = 'StageAlwaysClearReward';

    // ステージクリアランダム報酬
    case STAGE_RANDOM_CLEAR_REWARD = 'StageRandomClearReward';

    // ステージ初回クリア報酬
    case STAGE_FIRST_CLEAR_REWARD = 'StageFirstClearReward';

    // 降臨バトル
    case ADVENT_BATTLE_REWARD = 'AdventBattleReward';

    // 降臨バトルドロップ報酬
    case ADVENT_BATTLE_DROP_REWARD = 'AdventBattleDropReward';

    // 降臨バトル ランク報酬
    case ADVENT_BATTLE_RANK_REWARD = 'AdventBattleRankReward';

    // 降臨バトルクリア定常報酬
    case ADVENT_BATTLE_ALWAYS_CLEAR_REWARD = 'AdventBattleAlwaysClearReward';

    // 降臨バトルランダムクリア報酬
    case ADVENT_BATTLE_RANDOM_CLEAR_REWARD = 'AdventBattleRandomClearReward';

    // 降臨バトル初回クリア報酬
    case ADVENT_BATTLE_FIRST_CLEAR_REWARD = 'AdventBattleFirstClearReward';

    // 降臨バトル最高スコア報酬
    case ADVENT_BATTLE_MAX_SCORE_REWARD = 'AdventBattleMaxScoreReward';

    // 降臨バトル レイド合計スコア報酬
    case ADVENT_BATTLE_RAID_TOTAL_SCORE_REWARD = 'AdventBattleRaidTotalScoreReward';

    // ユーザー

    // レベルアップ報酬
    case USER_LEVEL_UP_REWARD = 'UserLevelUpReward';

    // 図鑑

    // 図鑑ランク報酬
    case UNIT_ENCYCLOPEDIA_REWARD = 'UnitEncyclopediaReward';

    // 図鑑新着コレクション報酬
    case ENCYCLOPEDIA_FIRST_COLLECTION_REWARD = 'EncyclopediaFirstCollectionReward';

    // 探索

    // 探索報酬
    case IDLE_INCENTIVE_REWARD = 'IdleIncentiveReward';

    // ジャンプ+

    // ジャンプ+連携報酬
    case JUMP_PLUS_REWARD = 'JumpPlusReward';

    /**
     * コスト消費経緯
     */

    // ショップ

    // ショップアイテム交換時に消費したコスト
    case TRADE_SHOP_ITEM_COST = 'TradeShopItemCost';

    // ステージ

    // ステージ挑戦時に消費したコスト
    case STAGE_CHALLENGE_COST = 'StageChallengeCost';

    // アイテム

    // かけらボックスからかけらへ変換する際に消費したコスト
    case ITEM_FRAGMENT_BOX_COST = 'ItemFragmentBoxCost';

    // Pvp挑戦時に消費したコスト
    case PVP_CHALLENGE_COST = 'PvpChallengeCost';

    // Pvpランク報酬
    case PVP_RANK_REWARD = 'PvpRankReward';

    // Pvpランキング報酬
    case PVP_RANKING_REWARD = 'PvpRankingReward';

    // Pvp累計ポイント報酬
    case PVP_TOTAL_SCORE_REWARD = 'PvpTotalScoreReward';

    /**
     * 機能コード
     */

    // キャラのかけら から 選択かけらBOX への交換
    case ITEM_TRADE_CHARACTER_FRAGMENT_TO_SELECTION_FRAGMENT_BOX = 'ItemTradeCharacterFragmentToSelectionFragmentBox';
}
