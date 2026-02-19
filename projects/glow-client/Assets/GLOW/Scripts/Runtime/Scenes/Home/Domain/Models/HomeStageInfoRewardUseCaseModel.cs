using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.Home.Domain.ValueObjects;

namespace GLOW.Scenes.Home.Domain.Models
{
    public record HomeStageInfoRewardResource(
        ResourceType ResourceType,
        MasterDataId ResourceId,
        PlayerResourceAmount ResourceAmount
    );

    public record HomeStageInfoRewardUseCaseModel(
        RewardCategory Category,
        AcquiredFlag AcquiredFlag,
        HomeStageInfoRewardResource HomeStageInfoRewardResource);
}
