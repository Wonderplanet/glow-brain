<?php

declare(strict_types=1);

namespace App\Constants;

use App\Domain\Mission\Enums\MissionCriterionType as ApiMissionCriterionType;
use Illuminate\Support\Collection;

enum MissionCriterionType: string
{
    case NONE = ApiMissionCriterionType::NONE->value;

    // ミッション
    case MISSION_CLEAR_COUNT = ApiMissionCriterionType::MISSION_CLEAR_COUNT->value;
    case SPECIFIC_MISSION_CLEAR_COUNT = ApiMissionCriterionType::SPECIFIC_MISSION_CLEAR_COUNT->value;
    case MISSION_BONUS_POINT = ApiMissionCriterionType::MISSION_BONUS_POINT->value;

    // ステージ
    case SPECIFIC_QUEST_CLEAR = ApiMissionCriterionType::SPECIFIC_QUEST_CLEAR->value;
    case SPECIFIC_STAGE_CLEAR_COUNT = ApiMissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT->value;
    case QUEST_CLEAR_COUNT = ApiMissionCriterionType::QUEST_CLEAR_COUNT->value;
    case STAGE_CLEAR_COUNT = ApiMissionCriterionType::STAGE_CLEAR_COUNT->value;
    case SPECIFIC_STAGE_CHALLENGE_COUNT = ApiMissionCriterionType::SPECIFIC_STAGE_CHALLENGE_COUNT->value;

    case SPECIFIC_UNIT_STAGE_CLEAR_COUNT = ApiMissionCriterionType::SPECIFIC_UNIT_STAGE_CLEAR_COUNT->value;
    case SPECIFIC_UNIT_STAGE_CHALLENGE_COUNT = ApiMissionCriterionType::SPECIFIC_UNIT_STAGE_CHALLENGE_COUNT->value;
    case SPECIFIC_TRIBE_UNIT_STAGE_CLEAR_COUNT = ApiMissionCriterionType::SPECIFIC_TRIBE_UNIT_STAGE_CLEAR_COUNT->value;
    case SPECIFIC_TRIBE_UNIT_STAGE_CHALLENGE_COUNT = ApiMissionCriterionType::SPECIFIC_TRIBE_UNIT_STAGE_CHALLENGE_COUNT->value;

    // インゲーム
    case DEFEAT_ENEMY_COUNT = ApiMissionCriterionType::DEFEAT_ENEMY_COUNT->value;
    case DEFEAT_BOSS_ENEMY_COUNT = ApiMissionCriterionType::DEFEAT_BOSS_ENEMY_COUNT->value;
    case SPECIFIC_ENEMY_DISCOVERY_COUNT = ApiMissionCriterionType::SPECIFIC_ENEMY_DISCOVERY_COUNT->value;
    case ENEMY_DISCOVERY_COUNT = ApiMissionCriterionType::ENEMY_DISCOVERY_COUNT->value;
    case SPECIFIC_SERIES_ENEMY_DISCOVERY_COUNT = ApiMissionCriterionType::SPECIFIC_SERIES_ENEMY_DISCOVERY_COUNT->value;

    // ログイン
    case LOGIN_COUNT = ApiMissionCriterionType::LOGIN_COUNT->value;
    case LOGIN_CONTINUE_COUNT = ApiMissionCriterionType::LOGIN_CONTINUE_COUNT->value;
    case DAYS_FROM_UNLOCKED_MISSION = ApiMissionCriterionType::DAYS_FROM_UNLOCKED_MISSION->value;

    // ユーザー
    case USER_LEVEL = ApiMissionCriterionType::USER_LEVEL->value;
    case ICON_CHANGE = ApiMissionCriterionType::ICON_CHANGE->value;
    case EMBLEM_CHANGE = ApiMissionCriterionType::EMBLEM_CHANGE->value;
    case TUTORIAL_COMPLETED = ApiMissionCriterionType::TUTORIAL_COMPLETED->value;
    case COIN_COLLECT = ApiMissionCriterionType::COIN_COLLECT->value;
    case COIN_USED_COUNT = ApiMissionCriterionType::COIN_USED_COUNT->value;

    // 図鑑
    case BOOK_EMBLEM_COUNT = ApiMissionCriterionType::BOOK_EMBLEM_COUNT->value;
    case BOOK_UNIT_COUNT = ApiMissionCriterionType::BOOK_UNIT_COUNT->value;

    // ユニット
    case UNIT_LEVEL = ApiMissionCriterionType::UNIT_LEVEL->value;
    case UNIT_LEVEL_UP_COUNT = ApiMissionCriterionType::UNIT_LEVEL_UP_COUNT->value;
    case SPECIFIC_UNIT_LEVEL = ApiMissionCriterionType::SPECIFIC_UNIT_LEVEL->value;
    case SPECIFIC_UNIT_RANK_UP_COUNT = ApiMissionCriterionType::SPECIFIC_UNIT_RANK_UP_COUNT->value;
    case SPECIFIC_UNIT_GRADE_UP_COUNT = ApiMissionCriterionType::SPECIFIC_UNIT_GRADE_UP_COUNT->value;
    case UNIT_ACQUIRED_COUNT = ApiMissionCriterionType::UNIT_ACQUIRED_COUNT->value;

    // ゲート
    case OUTPOST_ENHANCE_COUNT = ApiMissionCriterionType::OUTPOST_ENHANCE_COUNT->value;
    case SPECIFIC_OUTPOST_ENHANCE_LEVEL = ApiMissionCriterionType::SPECIFIC_OUTPOST_ENHANCE_LEVEL->value;
    case OUTPOST_KOMA_CHANGE = ApiMissionCriterionType::OUTPOST_KOMA_CHANGE->value;

    // システム
    case REVIEW_COMPLETE = ApiMissionCriterionType::REVIEW_COMPLETED->value;
    case FOLLOW_COMPLETED = ApiMissionCriterionType::FOLLOW_COMPLETED->value;
    case ACCOUNT_COMPLETED = ApiMissionCriterionType::ACCOUNT_COMPLETED->value;
    case IAA_COUNT = ApiMissionCriterionType::IAA_COUNT->value;
    case ACCESS_WEB = ApiMissionCriterionType::ACCESS_WEB->value;

    // ガチャ
    case SPECIFIC_GACHA_DRAW_COUNT = ApiMissionCriterionType::SPECIFIC_GACHA_DRAW_COUNT->value;
    case GACHA_DRAW_COUNT = ApiMissionCriterionType::GACHA_DRAW_COUNT->value;

    // アイテム
    case SPECIFIC_ITEM_COLLECT = ApiMissionCriterionType::SPECIFIC_ITEM_COLLECT->value;

    // 放置収益
    case IDLE_INCENTIVE_COUNT = ApiMissionCriterionType::IDLE_INCENTIVE_COUNT->value;
    case IDLE_INCENTIVE_QUICK_COUNT = ApiMissionCriterionType::IDLE_INCENTIVE_QUICK_COUNT->value;

    public function label(): string
    {
        return match ($this) {
            self::MISSION_CLEAR_COUNT => 'ミッションをY個クリアする',
            self::SPECIFIC_MISSION_CLEAR_COUNT => '指定したミッショングループXの内でY個クリアする',
            self::MISSION_BONUS_POINT => 'ミッションボーナスポイントをY個集める(ミッションの累計ボーナスポイントエリアの設定)',
            self::SPECIFIC_QUEST_CLEAR => '指定クエストXをクリアする',
            self::SPECIFIC_STAGE_CLEAR_COUNT => '指定ステージXをY回クリア',
            self::QUEST_CLEAR_COUNT => '通算クエストクリア回数がY回に到達',
            self::STAGE_CLEAR_COUNT => '通算ステージクリア回数がY回に到達',
            self::SPECIFIC_STAGE_CHALLENGE_COUNT => '指定ステージXにY回挑戦する',
            self::SPECIFIC_UNIT_STAGE_CLEAR_COUNT => '指定したユニットを編成して指定したステージを Y回クリア',
            self::SPECIFIC_UNIT_STAGE_CHALLENGE_COUNT => '指定したユニットを編成して指定したステージに Y回挑戦',
            self::SPECIFIC_TRIBE_UNIT_STAGE_CLEAR_COUNT => 'SpecificTribeUnitStageClearCount', //TODO 決まった際に対応
            self::SPECIFIC_TRIBE_UNIT_STAGE_CHALLENGE_COUNT => 'SpecificTribeUnitStageChallengeCount', //TODO 決まった際に対応
            self::DEFEAT_ENEMY_COUNT => 'インゲームで敵をY体撃破',
            self::DEFEAT_BOSS_ENEMY_COUNT => 'インゲームで強敵をY体撃破',
            self::SPECIFIC_ENEMY_DISCOVERY_COUNT => 'インゲームで指定敵キャラXをY体発見',
            self::ENEMY_DISCOVERY_COUNT => 'インゲームで敵キャラをY体発見,',
            self::SPECIFIC_SERIES_ENEMY_DISCOVERY_COUNT => '指定作品Xの敵キャラをY体発見',
            self::LOGIN_COUNT => '通算ログインがY日に到達',
            self::LOGIN_CONTINUE_COUNT => '連続ログインがY日目に到達',
            self::DAYS_FROM_UNLOCKED_MISSION => 'DaysFromUnlockedMission',//TODO 決まった際に対応
            self::USER_LEVEL => '全ユニットの内でいずれかがLv.Yに到達',
            self::ICON_CHANGE => 'EmblemChange', //TODO 決まった際に対応
            self::EMBLEM_CHANGE => 'EmblemChange', //TODO 決まった際に対応
            self::TUTORIAL_COMPLETED => 'チュートリアルをクリア',
            self::COIN_COLLECT => 'コインをX枚使用した',
            self::COIN_USED_COUNT => 'コインをX枚使用した',
            self::BOOK_EMBLEM_COUNT => 'BookEmblemCount', //TODO 決まった際に対応
            self::BOOK_UNIT_COUNT => 'BookUnitCount', //TODO 決まった際に対応
            self::UNIT_LEVEL => '全ユニットの内でいずれかがLv.Yに到達',
            self::UNIT_LEVEL_UP_COUNT => 'ユニットのレベルアップをY回する',
            self::SPECIFIC_UNIT_LEVEL => '指定ユニットがLv.Yに到達',
            self::SPECIFIC_UNIT_RANK_UP_COUNT => '指定したユニットのランクアップ回数がY回以上',
            self::SPECIFIC_UNIT_GRADE_UP_COUNT => '指定したユニットのグレードアップ回数がY回以上',
            self::UNIT_ACQUIRED_COUNT => 'ユニットをY体入手しよう',
            self::OUTPOST_ENHANCE_COUNT => 'ゲートをX回以上強化',
            self::SPECIFIC_OUTPOST_ENHANCE_LEVEL => '指定したゲート強化項目がLvYに到達する',
            self::OUTPOST_KOMA_CHANGE => 'OutpostKomaChange', //TODO 決まった際に対応
            self::REVIEW_COMPLETE => 'ストアレビューを記載',
            self::FOLLOW_COMPLETED => '公式X（エックス）をフォローする',
            self::ACCOUNT_COMPLETED => 'アカウント連携を行う',
            self::IAA_COUNT => '広告視聴をY回する',
            self::SPECIFIC_GACHA_DRAW_COUNT => '指定ガシャXをY回引く',
            self::GACHA_DRAW_COUNT => '通算でガチャをY回引く',
            self::SPECIFIC_ITEM_COLLECT => '指定アイテムをX個集める',
            self::IDLE_INCENTIVE_COUNT => '探索をY回する',
            self::IDLE_INCENTIVE_QUICK_COUNT => 'クイック探索をY回する',
            self::ACCESS_WEB => 'Webアクセスでミッションクリア'
        };
    }

    public static function labels(): Collection
    {
        $cases = self::cases();
        $labels = collect();
        foreach ($cases as $case) {
            if ($case === self::NONE) {
                continue;
            }
            $labels->put($case->value, $case->label());
        }
        return $labels;
    }
}
