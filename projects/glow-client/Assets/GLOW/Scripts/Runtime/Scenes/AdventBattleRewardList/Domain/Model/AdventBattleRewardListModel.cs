using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AdventBattle;

namespace GLOW.Scenes.AdventBattleRewardList.Domain.Model
{
    public record AdventBattleRewardListModel(
        AdventBattleType BattleType,
        AdventBattleScore PersonalScore,
        AdventBattleRaidTotalScore RaidTotalScore,
        RankType RankType,
        AdventBattleScoreRankLevel RankLevel,
        RemainingTimeSpan RemainingTimeSpan,
        IReadOnlyList<IAdventBattlePersonalRewardModel> PersonalRankingRewardModels,
        IReadOnlyList<IAdventBattlePersonalRewardModel> PersonalRankRewardModels,
        IReadOnlyList<AdventBattleRaidTotalScoreRewardModel> RaidTotalScoreRewardModels)
    {
        public static AdventBattleRewardListModel Empty { get; } = 
            new AdventBattleRewardListModel(
                AdventBattleType.ScoreChallenge,
                AdventBattleScore.Empty,
                AdventBattleRaidTotalScore.Empty,
                RankType.Bronze,
                AdventBattleScoreRankLevel.Empty,
                RemainingTimeSpan.Empty,
                new List<IAdventBattlePersonalRewardModel>(),
                new List<IAdventBattlePersonalRewardModel>(),
                new List<AdventBattleRaidTotalScoreRewardModel>());
    }
}