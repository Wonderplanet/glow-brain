using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.PvpRewardList.Presentation.ViewModel
{
    public record PvpRewardListViewModel(
        RemainingTimeSpan RemainingTimeSpan,
        IReadOnlyList<IPvpRankingRewardCellViewModel> RankingRewardCellViewModels,
        IReadOnlyList<PvpRankRewardCellViewModel> PointRankRewardCellViewModels,
        IReadOnlyList<PvpTotalScoreRewardCellViewModel> TotalPointRewardCellViewModels)
    {
        public static PvpRewardListViewModel Empty { get; } = 
            new PvpRewardListViewModel(
                RemainingTimeSpan.Empty,
                new List<IPvpRankingRewardCellViewModel>(),
                new List<PvpRankRewardCellViewModel>(),
                new List<PvpTotalScoreRewardCellViewModel>());

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}