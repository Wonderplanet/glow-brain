using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record UserArtworkFragmentModel(MasterDataId MstArtworkId, MasterDataId MstArtworkFragmentId)
    {
        public static UserArtworkFragmentModel Empty { get; } = new(MasterDataId.Empty, MasterDataId.Empty);

        public bool IsEmpty() => ReferenceEquals(this, Empty);
    };
}
