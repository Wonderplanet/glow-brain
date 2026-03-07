using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ArtworkEnhance.Domain.ValueObjects;

namespace GLOW.Scenes.ArtworkEnhance.Presentation.ViewModels
{
    public record ArtworkGradeUpRequiredIconViewModel(
        ItemIconAssetPath IconAssetPath,
        Rarity Rarity,
        ItemAmount Amount,
        ArtworkGradeUpItemEnoughFlag IsEnough);
}
