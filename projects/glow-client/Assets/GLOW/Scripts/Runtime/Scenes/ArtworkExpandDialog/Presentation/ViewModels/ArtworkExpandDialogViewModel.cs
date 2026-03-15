using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ArtworkFragment.Presentation.ViewModels;

namespace GLOW.Scenes.ArtworkExpandDialog.Presentation.ViewModels
{
    public record ArtworkExpandDialogViewModel(
        ArtworkName Name,
        ArtworkDescription Description,
        ArtworkAssetPath AssetPath,
        ArtworkFragmentPanelViewModel ArtworkFragmentPanelViewModel);
}
