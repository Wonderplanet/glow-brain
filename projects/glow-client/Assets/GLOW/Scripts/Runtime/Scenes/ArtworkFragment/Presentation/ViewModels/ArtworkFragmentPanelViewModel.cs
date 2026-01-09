using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.ArtworkFragment.Presentation.ViewModels
{
    public record ArtworkFragmentPanelViewModel(
        ArtworkAssetPath AssetPath,
        IReadOnlyList<ArtworkFragmentViewModel> ArtworkFragmentViewModelsComponents)
    {
        public static ArtworkFragmentPanelViewModel Empty { get; } = new(
            ArtworkAssetPath.Empty,
            new List<ArtworkFragmentViewModel>());
    }
}
