using System.Collections.Generic;
using Cysharp.Text;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pvp;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.PvpRewardList.Presentation.ViewModel
{
    public record PvpIntervalRankingRewardCellViewModel(
        MasterDataId Id,
        IReadOnlyList<PlayerResourceIconViewModel> Rewards,
        PvpRankingRank RankingRankLower,
        PvpRankingRank RankingRankUpper) : IPvpRankingRewardCellViewModel
    {
        public static PvpIntervalRankingRewardCellViewModel Empty { get; } = 
            new PvpIntervalRankingRewardCellViewModel(
                MasterDataId.Empty,
                new List<PlayerResourceIconViewModel>(),
                PvpRankingRank.Empty,
                PvpRankingRank.Empty);
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
        
        PvpRewardCategory IPvpRankingRewardCellViewModel.RewardCategory => PvpRewardCategory.Ranking;
        
        string IPvpRankingRewardCellViewModel.RankingText => ZString.Format(
            "{0} 位 ~ {1} 位", 
            RankingRankUpper.ToDisplayString(), 
            RankingRankLower.ToDisplayString());
    }
}