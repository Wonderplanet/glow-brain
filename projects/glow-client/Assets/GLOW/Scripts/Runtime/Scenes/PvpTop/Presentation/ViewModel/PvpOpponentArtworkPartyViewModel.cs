using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.PvpTop.Presentation.ViewModel
{
    public record PvpOpponentArtworkPartyViewModel(
        ArtworkAssetPath ArtworkAssetPath,
        Rarity Rarity,
        ArtworkGradeLevel Grade)
    {
        public static PvpOpponentArtworkPartyViewModel Empty { get; } = new(
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
