using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.OutpostArtworkChangeConfirm.Presentation.ViewModels
{
    public record OutpostArtworkChangeConfirmViewModel(
        ArtworkAssetPath BeforeArtworkSmallPath,
        ArtworkAssetPath AfterArtworkSmallPath);
}
