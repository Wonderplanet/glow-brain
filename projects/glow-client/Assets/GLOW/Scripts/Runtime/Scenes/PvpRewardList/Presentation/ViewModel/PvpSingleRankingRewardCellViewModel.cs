using System.Collections.Generic;
using Cysharp.Text;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pvp;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.PvpRewardList.Presentation.ViewModel
{
    public record PvpSingleRankingRewardCellViewModel(
        MasterDataId Id,
        IReadOnlyList<PlayerResourceIconViewModel> Rewards,
        PvpRankingRank RankingRank) : IPvpRankingRewardCellViewModel
    {
        public static PvpSingleRankingRewardCellViewModel Empty { get; } = 
            new PvpSingleRankingRewardCellViewModel(
                MasterDataId.Empty,
                new List<PlayerResourceIconViewModel>(),
                PvpRankingRank.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
        
        PvpRewardCategory IPvpRankingRewardCellViewModel.RewardCategory  =>
            PvpRewardCategory.Ranking;

        string IPvpRankingRewardCellViewModel.RankingText => ZString.Format("{0} 位", RankingRank.ToDisplayString());
        
        PvpRankingRank IPvpRankingRewardCellViewModel.RankingRankUpper => RankingRank;
    }
}