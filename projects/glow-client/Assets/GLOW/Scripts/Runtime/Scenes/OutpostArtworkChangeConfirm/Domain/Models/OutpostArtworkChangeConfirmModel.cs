using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.OutpostArtworkChangeConfirm.Domain.Models
{
    public record OutpostArtworkChangeConfirmModel(
        ArtworkAssetPath BeforeArtworkSmallPath,
        ArtworkAssetPath AfterArtworkSmallPath);
}
