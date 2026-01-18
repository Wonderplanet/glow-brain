using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.ArtworkFragment.Domain.Model
{
    public record ArtworkPanelModel(
        ArtworkAssetPath AssetPath,
        IReadOnlyList<ArtworkFragmentModel> ArtworkFragmentModels)
    {
        public static ArtworkPanelModel Empty { get; } = new(
            ArtworkAssetPath.Empty,
            new List<ArtworkFragmentModel>());
    }
}
