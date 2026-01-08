using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.AdventBattleRewardList.Presentation.ViewModel
{
    public record AdventBattleIntervalRankingRewardCellViewModel(
        MasterDataId Id,
        IReadOnlyList<PlayerResourceIconViewModel> Rewards,
        AdventBattleRankingRank RankingRankLower,
        AdventBattleRankingRank RankingRankUpper) : IAdventBattlePersonalCellViewModel
    {
        public static AdventBattleIntervalRankingRewardCellViewModel Empty { get; } = 
            new AdventBattleIntervalRankingRewardCellViewModel(
                MasterDataId.Empty,
                new List<PlayerResourceIconViewModel>(),
                AdventBattleRankingRank.Empty,
                AdventBattleRankingRank.Empty);
        

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
        
        AdventBattleRewardCategory IAdventBattlePersonalCellViewModel.RewardCategory  =>
            AdventBattleRewardCategory.Ranking;
    }
}