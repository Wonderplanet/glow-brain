using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.PvpRewardList.Domain.Model
{
    public interface IPvpRankingRewardModel
    {
        MasterDataId Id { get; }
        IReadOnlyList<PlayerResourceModel> Rewards { get; }
        PvpRewardCategory RewardCategory { get; }
    }
}