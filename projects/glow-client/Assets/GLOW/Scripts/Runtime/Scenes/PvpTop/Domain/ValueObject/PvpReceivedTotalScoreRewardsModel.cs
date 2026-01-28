using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.Pvp;
using GLOW.Modules.CommonReceiveView.Domain.Model;

namespace GLOW.Scenes.PvpTop.Domain.ValueObject
{
    public record PvpReceivedTotalScoreRewardsModel(
        IReadOnlyList<CommonReceiveResourceModel> TotalScoreRewards,
        PvpPoint ReceivedTotalScore)
    {
        public static PvpReceivedTotalScoreRewardsModel Empty { get; } = new(
            new List<CommonReceiveResourceModel>(),
            PvpPoint.Zero);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}