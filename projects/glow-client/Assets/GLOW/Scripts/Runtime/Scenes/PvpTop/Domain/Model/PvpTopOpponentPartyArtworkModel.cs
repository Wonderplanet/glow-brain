using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.PvpTop.Domain.Model
{
    public record PvpTopOpponentPartyArtworkModel(
        ArtworkAssetPath ArtworkAssetPath,
        Rarity Rarity,
        ArtworkGradeLevel Grade)
    {
        public static PvpTopOpponentPartyArtworkModel Empty { get; } = new(
            ArtworkAssetPath.Empty,
            Rarity.R,
            ArtworkGradeLevel.Empty
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
