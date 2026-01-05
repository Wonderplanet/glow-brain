using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record StageRewardResultModel(
        RewardCategory Category,
        RewardModel RewardModel,
        CampaignPercentage CampaignPercentage,
        StaminaLapNumber StaminaLapNumber)
    {
        public static StageRewardResultModel Empty { get; } = new (
            RewardCategory.Always,
            RewardModel.Empty,
            CampaignPercentage.Empty,
            StaminaLapNumber.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
