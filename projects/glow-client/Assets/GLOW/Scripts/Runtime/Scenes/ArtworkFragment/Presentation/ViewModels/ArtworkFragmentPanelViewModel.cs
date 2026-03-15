using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ArtworkEnhance.Domain.ValueObjects;

namespace GLOW.Scenes.ArtworkFragment.Presentation.ViewModels
{
    public record ArtworkFragmentPanelViewModel(
        ArtworkAssetPath AssetPath,
        ArtworkCompletedFlag IsCompleted,
        IReadOnlyList<ArtworkFragmentViewModel> ArtworkFragmentViewModelsComponents)
    {
        public static ArtworkFragmentPanelViewModel Empty { get; } = new(
            ArtworkAssetPath.Empty,
            ArtworkCompletedFlag.False,
            new List<ArtworkFragmentViewModel>());

        public bool IsAllLocked()
        {
            return ArtworkFragmentViewModelsComponents.Count == 0 && !IsCompleted;
        }
    }
}
