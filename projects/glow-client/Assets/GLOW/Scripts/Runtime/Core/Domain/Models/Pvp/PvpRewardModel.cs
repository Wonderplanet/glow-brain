using GLOW.Core.Domain.Constants;

namespace GLOW.Core.Domain.Models.Pvp
{
    public record PvpRewardModel(
        PvpRewardCategory RewardCategory,
        RewardModel Reward)
    {
        public static PvpRewardModel Empty { get; } = new(
            PvpRewardCategory.Ranking,
            RewardModel.Empty);

        public bool IsEmpty() => ReferenceEquals(this, Empty);
    }
}
