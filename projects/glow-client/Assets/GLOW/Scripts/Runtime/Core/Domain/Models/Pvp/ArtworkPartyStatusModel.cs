using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models.Pvp
{
    public record ArtworkPartyStatusModel(
        MasterDataId MstArtworkId,
        ArtworkGradeLevel ArtworkGradeLevel)
    {
        public static ArtworkPartyStatusModel Empty { get; } = new(
            MasterDataId.Empty,
            ArtworkGradeLevel.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
