using GLOW.Core.Domain.Constants;
using GLOW.Scenes.ArtworkEnhance.Domain.ValueObjects;

namespace GLOW.Scenes.ArtworkEnhance.Presentation.ViewModels
{
    public record ArtworkAcquisitionRouteCellViewModel(
        ArtworkAcquisitionRouteName ArtworkAcquisitionRouteName,
        ArtworkAcquisitionRouteType Type);
}
