using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record MstStageClearTimeRewardModel(
        MasterDataId MstStageId,
        StageClearTime UpperClearTimeMs,
        ResourceType ResourceType,
        MasterDataId ResourceId,
        ObscuredPlayerResourceAmount ResourceAmount)
    {
        public static MstStageClearTimeRewardModel Empty { get; } = new(
            MstStageId: MasterDataId.Empty,
            UpperClearTimeMs: StageClearTime.Empty,
            ResourceType: ResourceType.FreeDiamond,
            ResourceId: MasterDataId.Empty,
            ResourceAmount: ObscuredPlayerResourceAmount.Empty
        );

        public bool IsEmpty() => ReferenceEquals(this, Empty);
    }
}
