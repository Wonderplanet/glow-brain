using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.Pvp;

namespace GLOW.Scenes.PvpTop.Domain.ValueObject
{
    public record PvpTopNextTotalScoreRewardModel(
        PlayerResourceModel NextTotalScoreReward,
        PvpPoint NextTotalScore)
    {
        public static PvpTopNextTotalScoreRewardModel Empty { get; } = new(
            PlayerResourceModel.Empty,
            PvpPoint.Zero);
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}