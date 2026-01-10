using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.Home.Domain.ValueObjects;

namespace GLOW.Scenes.Home.Domain.Models
{
    public record HomeStageInfoSpeedAttackRewardUseCaseModel(
        HomeStageInfoRewardResource HomeStageInfoRewardResource,
        StageClearTime Time,
        AcquiredFlag AcquiredFlag);
}
