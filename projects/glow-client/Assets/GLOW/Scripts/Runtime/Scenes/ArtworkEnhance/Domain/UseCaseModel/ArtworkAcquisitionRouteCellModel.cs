using GLOW.Core.Domain.Constants;
using GLOW.Scenes.ArtworkEnhance.Domain.ValueObjects;

namespace GLOW.Scenes.ArtworkEnhance.Domain.UseCaseModel
{
    public record ArtworkAcquisitionRouteCellModel(
        ArtworkAcquisitionRouteName ArtworkAcquisitionRouteName,
        ArtworkAcquisitionRouteType Type);
}
