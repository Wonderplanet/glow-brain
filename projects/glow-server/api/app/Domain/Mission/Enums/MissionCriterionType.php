<?php

declare(strict_types=1);

namespace App\Domain\Mission\Enums;

use App\Domain\Mission\Entities\Criteria\AccessWebCriterion;
use App\Domain\Mission\Entities\Criteria\AccountCompletedCriterion;
use App\Domain\Mission\Entities\Criteria\AdventBattleChallengeCountCriterion;
use App\Domain\Mission\Entities\Criteria\AdventBattleScoreCriterion;
use App\Domain\Mission\Entities\Criteria\AdventBattleTotalScoreCriterion;
use App\Domain\Mission\Entities\Criteria\ArtworkCompletedCountCriterion;
use App\Domain\Mission\Entities\Criteria\CoinCollectCriterion;
use App\Domain\Mission\Entities\Criteria\CoinUsedCountCriterion;
use App\Domain\Mission\Entities\Criteria\DaysFromUnlockedMissionCriterion;
use App\Domain\Mission\Entities\Criteria\DefeatBossEnemyCountCriterion;
use App\Domain\Mission\Entities\Criteria\DefeatEnemyCountCriterion;
use App\Domain\Mission\Entities\Criteria\EmblemAcquiredCountCriterion;
use App\Domain\Mission\Entities\Criteria\EnemyDiscoveryCountCriterion;
use App\Domain\Mission\Entities\Criteria\FollowCompletedCriterion;
use App\Domain\Mission\Entities\Criteria\GachaDrawCountCriterion;
use App\Domain\Mission\Entities\Criteria\IaaCountCriterion;
use App\Domain\Mission\Entities\Criteria\IdleIncentiveCountCriterion;
use App\Domain\Mission\Entities\Criteria\IdleIncentiveQuickCountCriterion;
use App\Domain\Mission\Entities\Criteria\LoginContinueCountCriterion;
use App\Domain\Mission\Entities\Criteria\LoginCountCriterion;
use App\Domain\Mission\Entities\Criteria\MissionBonusPointCriterion;
use App\Domain\Mission\Entities\Criteria\MissionClearCountCriterion;
use App\Domain\Mission\Entities\Criteria\OutpostEnhanceCountCriterion;
use App\Domain\Mission\Entities\Criteria\PvpChallengeCountCriterion;
use App\Domain\Mission\Entities\Criteria\PvpWinCountCriterion;
use App\Domain\Mission\Entities\Criteria\QuestClearCountCriterion;
use App\Domain\Mission\Entities\Criteria\ReviewCompletedCriterion;
use App\Domain\Mission\Entities\Criteria\SpecificArtworkCompletedCountCriterion;
use App\Domain\Mission\Entities\Criteria\SpecificEmblemAcquiredCountCriterion;
use App\Domain\Mission\Entities\Criteria\SpecificEnemyDiscoveryCountCriterion;
use App\Domain\Mission\Entities\Criteria\SpecificGachaDrawCountCriterion;
use App\Domain\Mission\Entities\Criteria\SpecificItemCollectCriterion;
use App\Domain\Mission\Entities\Criteria\SpecificMissionClearCountCriterion;
use App\Domain\Mission\Entities\Criteria\SpecificOutpostEnhanceLevelCriterion;
use App\Domain\Mission\Entities\Criteria\SpecificQuestClearCriterion;
use App\Domain\Mission\Entities\Criteria\SpecificSeriesArtworkCompletedCountCriterion;
use App\Domain\Mission\Entities\Criteria\SpecificSeriesEmblemAcquiredCountCriterion;
use App\Domain\Mission\Entities\Criteria\SpecificSeriesEnemyDiscoveryCountCriterion;
use App\Domain\Mission\Entities\Criteria\SpecificSeriesUnitAcquiredCountCriterion;
use App\Domain\Mission\Entities\Criteria\SpecificStageChallengeCountCriterion;
use App\Domain\Mission\Entities\Criteria\SpecificStageClearCountCriterion;
use App\Domain\Mission\Entities\Criteria\SpecificUnitAcquiredCountCriterion;
use App\Domain\Mission\Entities\Criteria\SpecificUnitGradeUpCountCriterion;
use App\Domain\Mission\Entities\Criteria\SpecificUnitLevelCriterion;
use App\Domain\Mission\Entities\Criteria\SpecificUnitRankUpCountCriterion;
use App\Domain\Mission\Entities\Criteria\SpecificUnitStageChallengeCountCriterion;
use App\Domain\Mission\Entities\Criteria\SpecificUnitStageClearCountCriterion;
use App\Domain\Mission\Entities\Criteria\StageClearCountCriterion;
use App\Domain\Mission\Entities\Criteria\UnitAcquiredCountCriterion;
use App\Domain\Mission\Entities\Criteria\UnitLevelCriterion;
use App\Domain\Mission\Entities\Criteria\UnitLevelUpCountCriterion;
use App\Domain\Mission\Entities\Criteria\UserLevelCriterion;
use Illuminate\Support\Collection;

enum MissionCriterionType: string
{
    case NONE = 'None';

    // ミッション
    case MISSION_CLEAR_COUNT = 'MissionClearCount';
    case SPECIFIC_MISSION_CLEAR_COUNT = 'SpecificMissionClearCount';
    case MISSION_BONUS_POINT = 'MissionBonusPoint';

    // ステージ
    case SPECIFIC_QUEST_CLEAR = 'SpecificQuestClear';
    case SPECIFIC_STAGE_CLEAR_COUNT = 'SpecificStageClearCount';
    case QUEST_CLEAR_COUNT = 'QuestClearCount';
    case STAGE_CLEAR_COUNT = 'StageClearCount';
    case SPECIFIC_STAGE_CHALLENGE_COUNT = 'SpecificStageChallengeCount';

    case SPECIFIC_UNIT_STAGE_CLEAR_COUNT = 'SpecificUnitStageClearCount';
    case SPECIFIC_UNIT_STAGE_CHALLENGE_COUNT = 'SpecificUnitStageChallengeCount';
    case SPECIFIC_TRIBE_UNIT_STAGE_CLEAR_COUNT = 'SpecificTribeUnitStageClearCount';
    case SPECIFIC_TRIBE_UNIT_STAGE_CHALLENGE_COUNT = 'SpecificTribeUnitStageChallengeCount';

    // インゲーム
    case DEFEAT_ENEMY_COUNT = 'DefeatEnemyCount';
    case DEFEAT_BOSS_ENEMY_COUNT = 'DefeatBossEnemyCount';
    case SPECIFIC_SERIES_ENEMY_DISCOVERY_COUNT = 'SpecificSeriesEnemyDiscoveryCount';
    case ENEMY_DISCOVERY_COUNT = 'EnemyDiscoveryCount';
    case SPECIFIC_ENEMY_DISCOVERY_COUNT = 'SpecificEnemyDiscoveryCount';

    // ログイン
    case LOGIN_COUNT = 'LoginCount';
    case LOGIN_CONTINUE_COUNT = 'LoginContinueCount';
    case DAYS_FROM_UNLOCKED_MISSION = 'DaysFromUnlockedMission';

    // ユーザー
    case USER_LEVEL = 'UserLevel';
    case ICON_CHANGE = 'IconChange';
    case TUTORIAL_COMPLETED = 'TutorialCompleted';
    case COIN_COLLECT = 'CoinCollect';
    case COIN_USED_COUNT = 'CoinUsedCount';

    // 図鑑
    case SPECIFIC_SERIES_ARTWORK_COMPLETED_COUNT = 'SpecificSeriesArtworkCompletedCount';
    case ARTWORK_COMPLETED_COUNT = 'ArtworkCompletedCount';
    case SPECIFIC_ARTWORK_COMPLETED_COUNT = 'SpecificArtworkCompletedCount';

    // ユニット
    case UNIT_LEVEL = 'UnitLevel';
    case UNIT_LEVEL_UP_COUNT = 'UnitLevelUpCount';
    case SPECIFIC_UNIT_LEVEL = 'SpecificUnitLevel';
    case SPECIFIC_UNIT_RANK_UP_COUNT = 'SpecificUnitRankUpCount';
    case SPECIFIC_UNIT_GRADE_UP_COUNT = 'SpecificUnitGradeUpCount';
    case UNIT_ACQUIRED_COUNT = 'UnitAcquiredCount';
    case SPECIFIC_SERIES_UNIT_ACQUIRED_COUNT = 'SpecificSeriesUnitAcquiredCount';
    case SPECIFIC_UNIT_ACQUIRED_COUNT = 'SpecificUnitAcquiredCount';

    // ゲート
    case OUTPOST_ENHANCE_COUNT = 'OutpostEnhanceCount';
    case SPECIFIC_OUTPOST_ENHANCE_LEVEL = 'SpecificOutpostEnhanceLevel';
    case OUTPOST_KOMA_CHANGE = 'OutpostKomaChange';

    // システム
    case REVIEW_COMPLETED = 'ReviewCompleted';
    case FOLLOW_COMPLETED = 'FollowCompleted';
    case ACCOUNT_COMPLETED = 'AccountCompleted';
    case IAA_COUNT = 'IaaCount';
    case ACCESS_WEB = 'AccessWeb';

    // ガチャ
    case SPECIFIC_GACHA_DRAW_COUNT = 'SpecificGachaDrawCount';
    case GACHA_DRAW_COUNT = 'GachaDrawCount';

    // アイテム
    case SPECIFIC_ITEM_COLLECT = 'SpecificItemCollect';

    // エンブレム
    case SPECIFIC_SERIES_EMBLEM_ACQUIRED_COUNT = 'SpecificSeriesEmblemAcquiredCount';
    case EMBLEM_ACQUIRED_COUNT = 'EmblemAcquiredCount';
    case SPECIFIC_EMBLEM_ACQUIRED_COUNT = 'SpecificEmblemAcquiredCount';

    // 放置収益
    case IDLE_INCENTIVE_COUNT = 'IdleIncentiveCount';
    case IDLE_INCENTIVE_QUICK_COUNT = 'IdleIncentiveQuickCount';

    // 降臨バトル
    case ADVENT_BATTLE_CHALLENGE_COUNT = 'AdventBattleChallengeCount';
    case ADVENT_BATTLE_TOTAL_SCORE = 'AdventBattleTotalScore';
    case ADVENT_BATTLE_SCORE = 'AdventBattleScore';

    // PVP
    case PVP_CHALLENGE_COUNT = 'PvpChallengeCount';
    case PVP_WIN_COUNT = 'PvpWinCount';

    /**
     * Criterionクラスへのマッピング
     *
     * @return string|null
     */
    public function getCriterionClass(): ?string
    {
        return match ($this) {
                // ミッション
            self::MISSION_CLEAR_COUNT => MissionClearCountCriterion::class,
            self::SPECIFIC_MISSION_CLEAR_COUNT => SpecificMissionClearCountCriterion::class,
            self::MISSION_BONUS_POINT => MissionBonusPointCriterion::class,
                // ステージ
            self::SPECIFIC_STAGE_CLEAR_COUNT => SpecificStageClearCountCriterion::class,
            self::STAGE_CLEAR_COUNT => StageClearCountCriterion::class,
            self::SPECIFIC_STAGE_CHALLENGE_COUNT => SpecificStageChallengeCountCriterion::class,
            self::SPECIFIC_QUEST_CLEAR => SpecificQuestClearCriterion::class,
            self::SPECIFIC_UNIT_STAGE_CHALLENGE_COUNT => SpecificUnitStageChallengeCountCriterion::class,
            self::SPECIFIC_UNIT_STAGE_CLEAR_COUNT => SpecificUnitStageClearCountCriterion::class,
            self::QUEST_CLEAR_COUNT => QuestClearCountCriterion::class,
                // インゲーム
            self::DEFEAT_ENEMY_COUNT => DefeatEnemyCountCriterion::class,
            self::DEFEAT_BOSS_ENEMY_COUNT => DefeatBossEnemyCountCriterion::class,
            self::SPECIFIC_SERIES_ENEMY_DISCOVERY_COUNT => SpecificSeriesEnemyDiscoveryCountCriterion::class,
            self::ENEMY_DISCOVERY_COUNT => EnemyDiscoveryCountCriterion::class,
            self::SPECIFIC_ENEMY_DISCOVERY_COUNT => SpecificEnemyDiscoveryCountCriterion::class,
                // ログイン
            self::LOGIN_COUNT => LoginCountCriterion::class,
            self::LOGIN_CONTINUE_COUNT => LoginContinueCountCriterion::class,
            self::DAYS_FROM_UNLOCKED_MISSION => DaysFromUnlockedMissionCriterion::class,
                // ユーザー
            self::COIN_COLLECT => CoinCollectCriterion::class,
            self::COIN_USED_COUNT => CoinUsedCountCriterion::class,
            self::USER_LEVEL => UserLevelCriterion::class,
                // 図鑑
            self::SPECIFIC_SERIES_ARTWORK_COMPLETED_COUNT => SpecificSeriesArtworkCompletedCountCriterion::class,
            self::ARTWORK_COMPLETED_COUNT => ArtworkCompletedCountCriterion::class,
            self::SPECIFIC_ARTWORK_COMPLETED_COUNT => SpecificArtworkCompletedCountCriterion::class,
                // ユニット
            self::UNIT_LEVEL => UnitLevelCriterion::class,
            self::SPECIFIC_UNIT_LEVEL => SpecificUnitLevelCriterion::class,
            self::UNIT_LEVEL_UP_COUNT => UnitLevelUpCountCriterion::class,
            self::UNIT_ACQUIRED_COUNT => UnitAcquiredCountCriterion::class,
            self::SPECIFIC_UNIT_RANK_UP_COUNT => SpecificUnitRankUpCountCriterion::class,
            self::SPECIFIC_UNIT_GRADE_UP_COUNT => SpecificUnitGradeUpCountCriterion::class,
            self::SPECIFIC_UNIT_ACQUIRED_COUNT => SpecificUnitAcquiredCountCriterion::class,
            self::SPECIFIC_SERIES_UNIT_ACQUIRED_COUNT => SpecificSeriesUnitAcquiredCountCriterion::class,
                // ゲート
            self::OUTPOST_ENHANCE_COUNT => OutpostEnhanceCountCriterion::class,
            self::SPECIFIC_OUTPOST_ENHANCE_LEVEL => SpecificOutpostEnhanceLevelCriterion::class,
                // システム
            self::REVIEW_COMPLETED => ReviewCompletedCriterion::class,
            self::FOLLOW_COMPLETED => FollowCompletedCriterion::class,
            self::ACCOUNT_COMPLETED => AccountCompletedCriterion::class,
            self::IAA_COUNT => IaaCountCriterion::class,
            self::ACCESS_WEB => AccessWebCriterion::class,
                // ガチャ
            self::SPECIFIC_GACHA_DRAW_COUNT => SpecificGachaDrawCountCriterion::class,
            self::GACHA_DRAW_COUNT => GachaDrawCountCriterion::class,
                // アイテム
            self::SPECIFIC_ITEM_COLLECT => SpecificItemCollectCriterion::class,
                // エンブレム
            self::SPECIFIC_SERIES_EMBLEM_ACQUIRED_COUNT => SpecificSeriesEmblemAcquiredCountCriterion::class,
            self::EMBLEM_ACQUIRED_COUNT => EmblemAcquiredCountCriterion::class,
            self::SPECIFIC_EMBLEM_ACQUIRED_COUNT => SpecificEmblemAcquiredCountCriterion::class,
                // 放置収益
            self::IDLE_INCENTIVE_COUNT => IdleIncentiveCountCriterion::class,
            self::IDLE_INCENTIVE_QUICK_COUNT => IdleIncentiveQuickCountCriterion::class,
                // 降臨バトル
            self::ADVENT_BATTLE_CHALLENGE_COUNT => AdventBattleChallengeCountCriterion::class,
            self::ADVENT_BATTLE_TOTAL_SCORE => AdventBattleTotalScoreCriterion::class,
            self::ADVENT_BATTLE_SCORE => AdventBattleScoreCriterion::class,
                // PVP
            self::PVP_CHALLENGE_COUNT => PvpChallengeCountCriterion::class,
            self::PVP_WIN_COUNT => PvpWinCountCriterion::class,
            // その他
            default => null,
        };
    }

    /**
     * 複合ミッションの集計対象としてカウントして良いかどうかを判定する
     * true: カウントに含めて良い, false: カウントから除外する
     *
     * @param string $criterionType
     * @return boolean
     */
    public static function isCountableForCompositeMission(string $criterionType): bool
    {
        switch ($criterionType) {
            case MissionCriterionType::MISSION_CLEAR_COUNT->value:
            case MissionCriterionType::SPECIFIC_MISSION_CLEAR_COUNT->value:
            case MissionCriterionType::MISSION_BONUS_POINT->value:
                return false;
            default:
                return true;
        }
    }

    /**
     * 新規マスタ追加時に、即時達成判定が必要なCriterionTypeのリストを返す
     * @return Collection<self>
     */
    public static function needInstantClearTypes(): Collection
    {
        return collect([
            MissionCriterionType::SPECIFIC_UNIT_LEVEL,
            MissionCriterionType::SPECIFIC_UNIT_RANK_UP_COUNT,
            MissionCriterionType::SPECIFIC_UNIT_GRADE_UP_COUNT,
        ]);
    }
}
