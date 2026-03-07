using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ArtworkEnhance.Domain.ValueObjects;

namespace GLOW.Scenes.ArtworkFragment.Domain.Model
{
    public record ArtworkPanelModel(
        ArtworkAssetPath AssetPath,
        ArtworkCompletedFlag IsCompleted,
        IReadOnlyList<ArtworkFragmentModel> ArtworkFragmentModels)
    {
        public static ArtworkPanelModel Empty { get; } = new(
            ArtworkAssetPath.Empty,
            ArtworkCompletedFlag.False,
            new List<ArtworkFragmentModel>());
    }
}
