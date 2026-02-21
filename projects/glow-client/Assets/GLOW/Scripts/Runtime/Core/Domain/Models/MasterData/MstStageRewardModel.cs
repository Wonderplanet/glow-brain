using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record MstStageRewardModel(
        MasterDataId Id,
        MasterDataId MstStageId,
        RewardCategory RewardCategory,
        ResourceType ResourceType,
        MasterDataId ResourceId,
        ObscuredPlayerResourceAmount ResourceAmount,
        StageRewardPercentage Percentage,
        StageRewardSortOrder SortOrder);
}
