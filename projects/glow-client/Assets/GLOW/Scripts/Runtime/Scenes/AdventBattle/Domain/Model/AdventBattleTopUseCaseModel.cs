using System;
using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Constants.AdventBattle;
using GLOW.Core.Domain.Models.Campaign;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.PassShop.Domain.Model;

namespace GLOW.Scenes.AdventBattle.Domain.Model
{
    public record AdventBattleTopUseCaseModel(
        MasterDataId MstAdventBattleId,
        AdventBattleType BattleType,
        EventBonusGroupId EventBonusGroupId,
        AdventBattleChallengeCount ChallengeableCount,
        AdventBattleChallengeCount AdChallengeableCount,
        AdventBattleChallengeType AdventBattleChallengeType,
        AdventBattleScore TotalScore,
        AdventBattleScore RequiredLowerScore,
        AdventBattleScore MaxScore,
        AdventBattleScore HighScoreLastAnimationPlayed,
        AdventBattleRaidTotalScore RaidTotalScore,
        AdventBattleRaidTotalScore RequiredLowerNextRewardRaidTotalScore,
        RankType CurrentRankType,
        AdventBattleScoreRankLevel CurrentScoreRankLevel,
        UnitImageAssetPath DisplayEnemyUnitFirst,
        UnitImageAssetPath DisplayEnemyUnitSecond,
        UnitImageAssetPath DisplayEnemyUnitThird,
        KomaBackgroundAssetPath KomaBackgroundAssetPath,
        IReadOnlyList<AdventBattleHighScoreRewardModel> HighScoreRewards,
        IReadOnlyList<AdventBattleHighScoreRewardModel> HighScoreRewardsUpdated,
        RemainingTimeSpan AdventBattleRemainingTimeSpan,
        PartyName PartyName,
        ExistsSpecialRuleFlag ExistsSpecialRule,
        NotificationBadge MissionBadge,
        AdventBattleFirstRankingUpdateDateTime FirstRankingUpdateDateTime,
        AdventBattleName AdventBattleName,
        AdventBattleBossDescription AdventBattleBossDescription,
        HeldAdSkipPassInfoModel HeldAdSkipPassInfoModel,
        List<CampaignModel> CampaignModels)
    {
        public static AdventBattleTopUseCaseModel Empty = new AdventBattleTopUseCaseModel(
            MasterDataId.Empty,
            AdventBattleType.ScoreChallenge,
            EventBonusGroupId.Empty,
            AdventBattleChallengeCount.Empty,
            AdventBattleChallengeCount.Empty,
            AdventBattleChallengeType.Normal,
            AdventBattleScore.Empty,
            AdventBattleScore.Empty,
            AdventBattleScore.Empty,
            AdventBattleScore.Empty,
            AdventBattleRaidTotalScore.Empty,
            AdventBattleRaidTotalScore.Empty,
            RankType.Bronze,
            AdventBattleScoreRankLevel.Empty,
            UnitImageAssetPath.Empty,
            UnitImageAssetPath.Empty,
            UnitImageAssetPath.Empty,
            KomaBackgroundAssetPath.Empty,
            new List<AdventBattleHighScoreRewardModel>(),
            new List<AdventBattleHighScoreRewardModel>(),
            RemainingTimeSpan.Empty,
            PartyName.Empty,
            ExistsSpecialRuleFlag.False,
            NotificationBadge.False,
            AdventBattleFirstRankingUpdateDateTime.Empty,
            AdventBattleName.Empty,
            AdventBattleBossDescription.Empty,
            HeldAdSkipPassInfoModel.Empty,
            new List<CampaignModel>());

        public AdventBattleRankingCalculatingFlag CalculatingRankings(DateTimeOffset now)
        {
            return new AdventBattleRankingCalculatingFlag(FirstRankingUpdateDateTime > now);
        }

        public bool CanChallengeWithAd()
        {
            return ChallengeableCount.IsZero() && !AdChallengeableCount.IsZero();
        }

    }
}
