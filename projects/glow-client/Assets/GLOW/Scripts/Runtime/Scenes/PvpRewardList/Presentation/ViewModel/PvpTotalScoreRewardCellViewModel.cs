using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pvp;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.PvpRewardList.Presentation.ViewModel
{
    public record PvpTotalScoreRewardCellViewModel(
        MasterDataId Id,
        IReadOnlyList<PlayerResourceIconViewModel> Rewards,
        PvpPoint RequiredPoint,
        PvpRewardReceivedFlag IsReceived)
    {
        public static PvpTotalScoreRewardCellViewModel Empty { get; } = 
            new PvpTotalScoreRewardCellViewModel(
                MasterDataId.Empty,
                new List<PlayerResourceIconViewModel>(),
                PvpPoint.Empty,
                PvpRewardReceivedFlag.False);
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}