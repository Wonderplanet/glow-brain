using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pvp;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.PvpRewardList.Presentation.ViewModel
{
    public record PvpRankRewardCellViewModel(
        MasterDataId Id,
        IReadOnlyList<PlayerResourceIconViewModel> Rewards,
        PvpRankClassType RankType,
        PvpRankLevel RankLevel,
        PvpPoint RequiredPoint)
    {
        public static PvpRankRewardCellViewModel Empty { get; } = 
            new PvpRankRewardCellViewModel(
                MasterDataId.Empty,
                new List<PlayerResourceIconViewModel>(),
                PvpRankClassType.Bronze,
                PvpRankLevel.Empty,
                PvpPoint.Empty);
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}