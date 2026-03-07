using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.Home.Domain.ValueObjects;

namespace GLOW.Scenes.Home.Domain.Models
{
    public record HomeStageInfoArtworkFragmentResource(
        ResourceType ResourceType,
        MasterDataId ResourceId,
        PlayerResourceAmount ResourceAmount
    );

    public record HomeStageInfoArtworkFragmentUseCaseModel(
        HomeStageInfoArtworkFragmentResource HomeStageInfoArtworkFragmentResource,
        AcquiredFlag AcquiredFlag
    );
}
