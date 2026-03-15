using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ArtworkEnhance.Domain.ValueObjects;
using GLOW.Scenes.ArtworkFragment.Domain.Model;

namespace GLOW.Scenes.ArtworkExpandDialog.Domain.Models
{
    public record ArtworkExpandDialogModel(
        ArtworkName Name,
        ArtworkDescription Description,
        ArtworkAssetPath AssetPath,
        ArtworkCompletedFlag IsCompleted,
        IReadOnlyList<ArtworkFragmentModel> ArtworkFragmentModels);
}
