using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record UserArtworkModel(MasterDataId MstArtworkId, NewEncyclopediaFlag IsNewEncyclopedia)
    {
        public static UserArtworkModel Empty { get; } = new (MasterDataId.Empty, NewEncyclopediaFlag.False);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
