using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pvp;

namespace GLOW.Scenes.PvpRewardList.Domain.Model
{
    public record PvpTotalScoreRewardModel(
        MasterDataId Id,
        IReadOnlyList<PlayerResourceModel> Rewards,
        PvpPoint RequiredPoint,
        PvpRewardReceivedFlag IsReceived)
    {
        public static PvpTotalScoreRewardModel Empty { get; } = 
            new PvpTotalScoreRewardModel(
                MasterDataId.Empty,
                new List<PlayerResourceModel>(),
                PvpPoint.Empty,
                PvpRewardReceivedFlag.False);
    }
}