<?php

declare(strict_types=1);

namespace App\Constants;

use Illuminate\Support\Collection;
use App\Domain\Resource\Log\Enums\LogResourceTriggerSource as ApiLogResourceTriggerSource;

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
    case ITEM_REWARD = ApiLogResourceTriggerSource::ITEM_REWARD->value;

    // ミッション

    // アチーブメントミッションの達成報酬
    case MISSION_ACHIEVEMENT_REWARD = ApiLogResourceTriggerSource::MISSION_ACHIEVEMENT_REWARD->value;

    // 初心者ミッションの達成報酬
    case MISSION_BEGINNER_REWARD = ApiLogResourceTriggerSource::MISSION_BEGINNER_REWARD->value;

    // デイリーミッションの達成報酬
    case MISSION_DAILY_REWARD = ApiLogResourceTriggerSource::MISSION_DAILY_REWARD->value;

    // ウィークリーミッションの達成報酬
    case MISSION_WEEKLY_REWARD = ApiLogResourceTriggerSource::MISSION_WEEKLY_REWARD->value;

    // イベントミッションの達成報酬
    case MISSION_EVENT_REWARD = ApiLogResourceTriggerSource::MISSION_EVENT_REWARD->value;

    // イベントデイリーミッションの達成報酬
    case MISSION_EVENT_DAILY_REWARD = ApiLogResourceTriggerSource::MISSION_EVENT_DAILY_REWARD->value;

    // 期間限定ミッションの達成報酬
    case MISSION_LIMITED_TERM_REWARD = ApiLogResourceTriggerSource::MISSION_LIMITED_TERM_REWARD->value;

    // デイリーボーナスの達成報酬
    case MISSION_DAILY_BONUS_REWARD = ApiLogResourceTriggerSource::MISSION_DAILY_BONUS_REWARD->value;

    // イベントデイリーボーナスの達成報酬
    case MISSION_EVENT_DAILY_BONUS_REWARD = ApiLogResourceTriggerSource::MISSION_EVENT_DAILY_BONUS_REWARD->value;

    // カムバックボーナスの達成報酬
    case COMEBACK_BONUS_REWARD = ApiLogResourceTriggerSource::COMEBACK_BONUS_REWARD->value;

    // メッセージ

    // 運営配布メッセージからの配布物報酬
    case MESSAGE_REWARD = ApiLogResourceTriggerSource::MESSAGE_REWARD->value;

    // システムメッセージ(ユーザー未受取報酬など)の報酬
    case SYSTEM_MESSAGE_REWARD = ApiLogResourceTriggerSource::SYSTEM_MESSAGE_REWARD->value;

    // ショップ

    // ショップアイテム交換物
    case SHOP_ITEM_REWARD = ApiLogResourceTriggerSource::SHOP_ITEM_REWARD->value;

    // ショップパックで受け取った報酬
    case SHOP_PACK_CONTENT_REWARD = ApiLogResourceTriggerSource::SHOP_PACK_CONTENT_REWARD->value;

    // ショップで商品購入(リアルマネーを使用)して受け取った購入物
    case SHOP_PURCHASED_REWARD = ApiLogResourceTriggerSource::SHOP_PURCHASED_REWARD->value;

    // ガシャ

    // ガシャ排出物
    case GACHA_REWARD = ApiLogResourceTriggerSource::GACHA_REWARD->value;

    // ステージ

    // ステージクリア定常報酬
    case STAGE_ALWAYS_CLEAR_REWARD = ApiLogResourceTriggerSource::STAGE_ALWAYS_CLEAR_REWARD->value;

    // ステージクリアランダム報酬
    case STAGE_RANDOM_CLEAR_REWARD = ApiLogResourceTriggerSource::STAGE_RANDOM_CLEAR_REWARD->value;

    // ステージ初回クリア報酬
    case STAGE_FIRST_CLEAR_REWARD = ApiLogResourceTriggerSource::STAGE_FIRST_CLEAR_REWARD->value;

    // 降臨バトル
    // TODO: 降臨バトル報酬色々あるので整理して分離する
    case ADVENT_BATTLE_REWARD = ApiLogResourceTriggerSource::ADVENT_BATTLE_REWARD->value;

    // 降臨バトルドロップ報酬
    case ADVENT_BATTLE_DROP_REWARD = ApiLogResourceTriggerSource::ADVENT_BATTLE_DROP_REWARD->value;

    // 降臨バトルクリア定常報酬
    case ADVENT_BATTLE_ALWAYS_CLEAR_REWARD = ApiLogResourceTriggerSource::ADVENT_BATTLE_ALWAYS_CLEAR_REWARD->value;

    // 降臨バトルランダムクリア報酬
    case ADVENT_BATTLE_RANDOM_CLEAR_REWARD = ApiLogResourceTriggerSource::ADVENT_BATTLE_RANDOM_CLEAR_REWARD->value;

    // 降臨バトル初回クリア報酬
    case ADVENT_BATTLE_FIRST_CLEAR_REWARD = ApiLogResourceTriggerSource::ADVENT_BATTLE_FIRST_CLEAR_REWARD->value;

    // ユーザー

    // レベルアップ報酬
    case USER_LEVEL_UP_REWARD = ApiLogResourceTriggerSource::USER_LEVEL_UP_REWARD->value;

    // 図鑑

    // 図鑑ランク報酬
    case UNIT_ENCYCLOPEDIA_REWARD = ApiLogResourceTriggerSource::UNIT_ENCYCLOPEDIA_REWARD->value;

    // 探索

    // 探索報酬
    case IDLE_INCENTIVE_REWARD = ApiLogResourceTriggerSource::IDLE_INCENTIVE_REWARD->value;

    // ジャンプ+

    // ジャンプ+連携報酬
    case JUMP_PLUS_REWARD = ApiLogResourceTriggerSource::JUMP_PLUS_REWARD->value;

    /**
     * コスト消費経緯
     */

    // ショップ

    // ショップアイテム交換時に消費したコスト
    case TRADE_SHOP_ITEM_COST = ApiLogResourceTriggerSource::TRADE_SHOP_ITEM_COST->value;

    // ステージ

    // ステージ挑戦時に消費したコスト
    case STAGE_CHALLENGE_COST = ApiLogResourceTriggerSource::STAGE_CHALLENGE_COST->value;

    // アイテム

    // かけらボックスからかけらへ変換する際に消費したコスト
    case ITEM_FRAGMENT_BOX_COST = ApiLogResourceTriggerSource::ITEM_FRAGMENT_BOX_COST->value;

    /**
     * 機能コード
     */

    // アイテム交換所

    // キャラのかけら から 選択かけらBOX への交換
    case ITEM_TRADE_CHARACTER_FRAGMENT_TO_SELECTION_FRAGMENT_BOX = ApiLogResourceTriggerSource::ITEM_TRADE_CHARACTER_FRAGMENT_TO_SELECTION_FRAGMENT_BOX->value;


    public function label(): string
    {
        return match ($this) {
            self::ITEM_REWARD => 'かけらボックスから変換して得られた報酬',
            self::MISSION_ACHIEVEMENT_REWARD => 'アチーブメントミッションの達成報酬',
            self::MISSION_BEGINNER_REWARD => '初心者ミッションの達成報酬',
            self::MISSION_DAILY_REWARD => 'デイリーミッションの達成報酬',
            self::MISSION_WEEKLY_REWARD => 'ウィークリーミッションの達成報酬',
            self::MISSION_EVENT_REWARD => 'イベントミッションの達成報酬',
            self::MISSION_EVENT_DAILY_REWARD => 'イベントデイリーミッションの達成報酬',
            self::MISSION_LIMITED_TERM_REWARD => '期間限定ミッションの達成報酬',
            self::MISSION_DAILY_BONUS_REWARD => 'デイリーボーナスの達成報酬',
            self::MISSION_EVENT_DAILY_BONUS_REWARD => 'イベントデイリーボーナスの達成報酬',
            self::COMEBACK_BONUS_REWARD => 'カムバックボーナスの達成報酬',
            self::MESSAGE_REWARD => '運営配布メッセージからの配布物報酬',
            self::SYSTEM_MESSAGE_REWARD => 'システムメッセージ(ユーザー未受取報酬など)の報酬',
            self::SHOP_ITEM_REWARD => 'ショップアイテム交換物',
            self::SHOP_PACK_CONTENT_REWARD => 'ショップパックで受け取った報酬',
            self::SHOP_PURCHASED_REWARD => 'ショップで商品購入(リアルマネーを使用)して受け取った購入物',
            self::GACHA_REWARD => 'ガシャ排出物',
            self::STAGE_ALWAYS_CLEAR_REWARD => 'ステージクリア定常報酬',
            self::STAGE_RANDOM_CLEAR_REWARD => 'ステージクリアランダム報酬',
            self::STAGE_FIRST_CLEAR_REWARD => 'ステージ初回クリア報酬',
            self::ADVENT_BATTLE_REWARD => '降臨バトル報酬',
            self::ADVENT_BATTLE_DROP_REWARD => '降臨バトルドロップ報酬',
            self::ADVENT_BATTLE_ALWAYS_CLEAR_REWARD => '降臨バトルクリア定常報酬',
            self::ADVENT_BATTLE_RANDOM_CLEAR_REWARD => '降臨バトルランダムクリア報酬',
            self::ADVENT_BATTLE_FIRST_CLEAR_REWARD => '降臨バトル初回クリア報酬',
            self::USER_LEVEL_UP_REWARD => 'レベルアップ報酬',
            self::UNIT_ENCYCLOPEDIA_REWARD => '図鑑ランク報酬',
            self::IDLE_INCENTIVE_REWARD => '探索報酬',
            self::JUMP_PLUS_REWARD => 'ジャンプ+連携報酬',
            self::TRADE_SHOP_ITEM_COST => 'ショップアイテム交換時に消費したコスト',
            self::STAGE_CHALLENGE_COST => 'ステージ挑戦時に消費したコスト',
            self::ITEM_FRAGMENT_BOX_COST => 'かけらボックスからかけらへ変換する際に消費したコスト',
            self::ITEM_TRADE_CHARACTER_FRAGMENT_TO_SELECTION_FRAGMENT_BOX => 'キャラのかけら から 選択かけらBOX への交換',
            default => '',
        };
    }

    public static function labels(): Collection
    {
        $cases = self::cases();
        $labels = collect();
        foreach ($cases as $case) {
            $labels->put($case->value, $case->label());
        }
        return $labels;
    }
}
