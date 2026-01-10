using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record MstConfigKey(ObscuredString Value)
    {
        public static MstConfigKey Empty = new MstConfigKey(string.Empty);
        public static MstConfigKey UnitLevelCap => new MstConfigKey("UNIT_LEVEL_CAP");
        public static MstConfigKey UnitStatusExponent => new MstConfigKey("UNIT_STATUS_EXPONENT");
        public static MstConfigKey SpecialUnitStatusExponent => new MstConfigKey("SPECIAL_UNIT_STATUS_EXPONENT");
        public static MstConfigKey MaxDailyBuyStaminaAdCount => new MstConfigKey("MAX_DAILY_BUY_STAMINA_AD_COUNT");
        public static MstConfigKey DailyBuyStaminaAdIntervalMinutes => new MstConfigKey("DAILY_BUY_STAMINA_AD_INTERVAL_MINUTES");
        public static MstConfigKey RecoveryStaminaMinute => new MstConfigKey("RECOVERY_STAMINA_MINUTE");
        public static MstConfigKey BuyStaminaDiamondAmount => new MstConfigKey("BUY_STAMINA_DIAMOND_AMOUNT");
        public static MstConfigKey BuyStaminaAdPercentageOfMaxStamina => new MstConfigKey("BUY_STAMINA_AD_PERCENTAGE_OF_MAX_STAMINA");
        public static MstConfigKey BuyStaminaDiamondPercentageOfMaxStamina => new MstConfigKey("BUY_STAMINA_DIAMOND_PERCENTAGE_OF_MAX_STAMINA");
        public static MstConfigKey StageContinueDiamondAmount => new MstConfigKey("STAGE_CONTINUE_DIAMOND_AMOUNT");
        public static MstConfigKey UserFreeDiamondMaxAmount => new MstConfigKey("USER_FREE_DIAMOND_MAX_AMOUNT");
        public static MstConfigKey UserPaidDiamondMaxAmount => new MstConfigKey("USER_PAID_DIAMOND_MAX_AMOUNT");
        public static MstConfigKey UserItemMaxAmount => new MstConfigKey("USER_ITEM_MAX_AMOUNT");
        public static MstConfigKey UserCoinMaxAmount => new MstConfigKey("USER_COIN_MAX_AMOUNT");
        public static MstConfigKey UserExpMaxAmount => new MstConfigKey("USER_EXP_MAX_AMOUNT");
        public static MstConfigKey UserMaxStaminaAmount => new MstConfigKey("USER_STAMINA_MAX_AMOUNT");
        public static MstConfigKey UserEmblemMaxAmount => new MstConfigKey("USER_EMBLEM_MAX_AMOUNT");
        public static MstConfigKey InGameInitializeBattlePoint => new MstConfigKey("IN_GAME_INITIALIZE_BATTLE_POINT");
        public static MstConfigKey InGameMaxBattlePoint => new MstConfigKey("IN_GAME_MAX_BATTLE_POINT");
        public static MstConfigKey InGameBattlePointChargeAmount => new MstConfigKey("IN_GAME_BATTLE_POINT_CHARGE_AMOUNT");
        public static MstConfigKey InGameBattlePointChargeInterval => new MstConfigKey("IN_GAME_BATTLE_POINT_CHARGE_INTERVAL");
        public static MstConfigKey EnhanceQuestChallengeLimit => new MstConfigKey("ENHANCE_QUEST_CHALLENGE_LIMIT");
        public static MstConfigKey EnhanceQuestChallengeAdLimit => new MstConfigKey("ENHANCE_QUEST_CHALLENGE_AD_LIMIT");
        public static MstConfigKey PartySpecialUnitAssignLimit => new MstConfigKey("PARTY_SPECIAL_UNIT_ASSIGN_LIMIT");
        public static MstConfigKey RushDamageCoefficient => new MstConfigKey("RUSH_DAMAGE_COEFFICIENT");
        public static MstConfigKey RushGaugeChargeFirst => new MstConfigKey("RUSH_GAUGE_CHARGE_FIRST");
        public static MstConfigKey RushGaugeChargeSecond => new MstConfigKey("RUSH_GAUGE_CHARGE_SECOND");
        public static MstConfigKey RushGaugeChargeThird => new MstConfigKey("RUSH_GAUGE_CHARGE_THIRD");
        public static MstConfigKey RushKnockBackTypeFirst => new MstConfigKey("RUSH_KNOCK_BACK_TYPE_FIRST");
        public static MstConfigKey RushKnockBackTypeSecond => new MstConfigKey("RUSH_KNOCK_BACK_TYPE_SECOND");
        public static MstConfigKey RushKnockBackTypeThird => new MstConfigKey("RUSH_KNOCK_BACK_TYPE_THIRD");
        public static MstConfigKey RushMaxDamage => new MstConfigKey("RUSH_MAX_DAMAGE");
        public static MstConfigKey RushDefaultChargeTime => new MstConfigKey("RUSH_DEFAULT_CHARGE_TIME");
        public static MstConfigKey RushMinChargeTime => new MstConfigKey("RUSH_MIN_CHARGE_TIME");
        public static MstConfigKey FreezeDamageIncreasePercentage => new MstConfigKey("FREEZE_DAMAGE_INCREASE_PERCENTAGE");
        public static MstConfigKey AdventBattleRankingUpdateIntervalMinutes => new MstConfigKey("ADVENT_BATTLE_RANKING_UPDATE_INTERVAL_MINUTES");
        public static MstConfigKey AdventBattleRankingAggregateHours => new MstConfigKey("ADVENT_BATTLE_RANKING_AGGREGATE_HOURS");
        public static MstConfigKey LocalNotificationIdleIncentiveHours => new MstConfigKey("LOCAL_NOTIFICATION_IDLE_INCENTIVE_HOURS");
        public static MstConfigKey LocalNotificationDailyMissionHours => new MstConfigKey("LOCAL_NOTIFICATION_DAILY_MISSION_HOURS");
        public static MstConfigKey LocalNotificationBeginnerMissionAfterHours => new MstConfigKey("LOCAL_NOTIFICATION_BEGINNER_MISSION_AFTER_HOURS");
        public static MstConfigKey LocalNotificationCoinQuestHours => new MstConfigKey("LOCAL_NOTIFICATION_COIN_QUEST_HOURS");
        public static MstConfigKey LocalNotificationAdGachaHours => new MstConfigKey("LOCAL_NOTIFICATION_AD_GACHA_HOURS");
        public static MstConfigKey LocalNotificationLoginAfterHoursOne => new MstConfigKey("LOCAL_NOTIFICATION_LOGIN_AFTER_HOURS_ONE");
        public static MstConfigKey LocalNotificationLoginAfterHoursTwo => new MstConfigKey("LOCAL_NOTIFICATION_LOGIN_AFTER_HOURS_TWO");
        public static MstConfigKey LocalNotificationLoginAfterHoursThree => new MstConfigKey("LOCAL_NOTIFICATION_LOGIN_AFTER_HOURS_THREE");
        public static MstConfigKey LocalNotificationLoginAfterHoursFour => new MstConfigKey("LOCAL_NOTIFICATION_LOGIN_AFTER_HOURS_FOUR");
        public static MstConfigKey LocalNotificationLoginAfterHoursFive => new MstConfigKey("LOCAL_NOTIFICATION_LOGIN_AFTER_HOURS_FIVE");
        public static MstConfigKey LocalNotificationTutorialGachaAfterHours => new MstConfigKey("LOCAL_NOTIFICATION_TUTORIAL_GACHA_AFTER_HOURS");
        public static MstConfigKey LocalNotificationEventOrAdventBattleCountHours => new MstConfigKey("LOCAL_NOTIFICATION_EVENT_OR_ADVENT_BATTLE_COUNT_HOURS");
        public static MstConfigKey LocalNotificationPvpCountHours => new MstConfigKey("LOCAL_NOTIFICATION_PVP_COUNT_HOURS");
        public static MstConfigKey InAppReviewTriggerQuestId1 => new MstConfigKey("IN_APP_REVIEW_TRIGGER_QUEST_ID_1");
        public static MstConfigKey InAppReviewTriggerQuestId2 => new MstConfigKey("IN_APP_REVIEW_TRIGGER_QUEST_ID_2");
        public static MstConfigKey AdContinueMaxCount => new MstConfigKey("AD_CONTINUE_MAX_COUNT");
        public static MstConfigKey PvpChallengeItemId => new MstConfigKey("PVP_CHALLENGE_ITEM_ID");
        public static MstConfigKey PvpOpponentRefreshCoolTimeSeconds => new MstConfigKey("PVP_OPPONENT_REFRESH_COOLTIME_SECONDS");
        public static MstConfigKey PvpTopApiRequestCoolTimeMinute => new MstConfigKey("PVP_TOP_API_REQUEST_COOLTIME_MINUTES");
        public static MstConfigKey IdleIncentiveDefaultKomaBackgroundAssetKey => new MstConfigKey("IDLE_INCENTIVE_DEFAULT_KOMA_BACKGROUND_ASSET_KEY");
        public static MstConfigKey IdleIncentiveDefaultEnemyAssetKey => new MstConfigKey("IDLE_INCENTIVE_DEFAULT_ENEMY_ASSET_KEY");
        public static MstConfigKey IdleIncentiveInitialRewardMstStageId => new MstConfigKey("IDLE_INCENTIVE_INITIAL_REWARD_MST_STAGE_ID");
        public static MstConfigKey GachaStartDashOprId => new MstConfigKey("GACHA_START_DASH_OPR_ID");
        public static MstConfigKey DefaultOutpostArtworkId => new MstConfigKey("DEFAULT_OUTPOST_ARTWORK_ID");
        public static MstConfigKey AnnouncementHookedPatternUrl => new MstConfigKey("ANNOUNCEMENT_HOOK_PATTERN_URL");
    }
}
