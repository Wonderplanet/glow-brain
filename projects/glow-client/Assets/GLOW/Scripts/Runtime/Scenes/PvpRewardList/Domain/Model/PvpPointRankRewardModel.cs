using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pvp;

namespace GLOW.Scenes.PvpRewardList.Domain.Model
{
    public record PvpPointRankRewardModel(
        MasterDataId Id,
        IReadOnlyList<PlayerResourceModel> Rewards,
        PvpRankClassType RankType,
        PvpRankLevel RankLevel,
        PvpPoint RequiredPoint)
    {
        public static PvpPointRankRewardModel Empty { get; } = 
            new PvpPointRankRewardModel(
                MasterDataId.Empty,
                new List<PlayerResourceModel>(),
                PvpRankClassType.Bronze,
                PvpRankLevel.Empty,
                PvpPoint.Empty);
    }
}