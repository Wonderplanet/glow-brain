using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.AdventBattleRewardList.Presentation.ViewModel
{
    public record AdventBattleSingleRankingRewardCellViewModel(
        MasterDataId Id,
        IReadOnlyList<PlayerResourceIconViewModel> Rewards,
        AdventBattleRankingRank RankingRank) : IAdventBattlePersonalCellViewModel
    {
        public static AdventBattleSingleRankingRewardCellViewModel Empty { get; } = 
            new AdventBattleSingleRankingRewardCellViewModel(
                MasterDataId.Empty,
                new List<PlayerResourceIconViewModel>(),
                AdventBattleRankingRank.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
        
        AdventBattleRewardCategory IAdventBattlePersonalCellViewModel.RewardCategory =>
            AdventBattleRewardCategory.Ranking;
    }
}