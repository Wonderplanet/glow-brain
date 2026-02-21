using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record MstStageEventRewardModel(
        MasterDataId Id,
        MasterDataId MstStageId,
        RewardCategory RewardCategory,
        ResourceType ResourceType,
        MasterDataId ResourceId,
        Percentage Percentage,
        ObscuredPlayerResourceAmount ResourceAmount,
        SortOrder SortOrder)
    {
        public static MstStageEventRewardModel Empty { get; } = new(
            MasterDataId.Empty,
            MasterDataId.Empty,
            RewardCategory.Always,
            ResourceType.FreeDiamond,
            MasterDataId.Empty,
            Percentage.Zero,
            ObscuredPlayerResourceAmount.Empty,
            SortOrder.Zero
        );
        public bool IsEmpty() => ReferenceEquals(this, Empty);
    }
}
