using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models.Mission
{
    public record MissionRewardModel(
        MissionType MissionType,
        MasterDataId MissionId,
        RewardModel RewardModel)
    {
        public static MissionRewardModel Empty { get; } = new(
            MissionType.Achievement,
            MasterDataId.Empty,
            RewardModel.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
