using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.ArtworkExpandDialog.Presentation.ViewModels
{
    public record ArtworkExpandDialogViewModel(
        ArtworkName Name,
        ArtworkDescription Description,
        ArtworkAssetPath AssetPath);
}
