using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models.Gacha;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record RewardModel(
        MasterDataId ResourceId,
        ResourceType ResourceType,
        PlayerResourceAmount Amount,
        UnreceivedRewardReasonType UnreceivedRewardReasonType,
        PreConversionResourceModel PreConversionResource)
    {
        public static RewardModel Empty { get; } = new(
            MasterDataId.Empty,
            ResourceType.Item,
            PlayerResourceAmount.Empty,
            UnreceivedRewardReasonType.None,
            PreConversionResourceModel.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
