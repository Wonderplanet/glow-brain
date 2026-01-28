using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pvp;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.PvpRewardList.Presentation.ViewModel
{
    public interface IPvpRankingRewardCellViewModel
    {
        MasterDataId Id { get; }
        IReadOnlyList<PlayerResourceIconViewModel> Rewards { get; }
        PvpRewardCategory RewardCategory { get; }
        PvpRankingRank RankingRankUpper { get; }
        string RankingText { get; }
    }
}