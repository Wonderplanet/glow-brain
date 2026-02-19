using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AdventBattle;

namespace GLOW.Scenes.AdventBattleRewardList.Presentation.ViewModel
{
    public record AdventBattleRewardListViewModel(
        AdventBattleType AdventBattleType,
        AdventBattleScore MyScore,
        AdventBattleRaidTotalScore RaidTotalScore,
        RankType RankType,
        AdventBattleScoreRankLevel RankLevel,
        RemainingTimeSpan RemainingTimeSpan,
        IReadOnlyList<IAdventBattlePersonalCellViewModel> PersonalRankingRewardCellViewModels,
        IReadOnlyList<IAdventBattlePersonalCellViewModel> PersonalRankRewardCellViewModels,
        IReadOnlyList<AdventBattleRaidTotalScoreRewardCellViewModel> RaidTotalScoreRewardCellViewModels)
    {
        public static AdventBattleRewardListViewModel Empty { get; } = 
            new AdventBattleRewardListViewModel(
                AdventBattleType.ScoreChallenge,
                AdventBattleScore.Empty,
                AdventBattleRaidTotalScore.Empty,
                RankType.Bronze,
                AdventBattleScoreRankLevel.Empty,
                RemainingTimeSpan.Empty,
                new List<IAdventBattlePersonalCellViewModel>(),
                new List<IAdventBattlePersonalCellViewModel>(),
                new List<AdventBattleRaidTotalScoreRewardCellViewModel>());
    }
}