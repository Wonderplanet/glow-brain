using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.ArtworkExpandDialog.Domain.Models
{
    public record ArtworkExpandDialogModel(ArtworkName Name, ArtworkDescription Description, ArtworkAssetPath AssetPath);
}
