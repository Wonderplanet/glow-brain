using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record UserArtworkModel(
        MasterDataId MstArtworkId,
        NewEncyclopediaFlag IsNewEncyclopedia,
        ArtworkGradeLevel Grade)
    {
        public static UserArtworkModel Empty { get; } = new (
            MasterDataId.Empty,
            NewEncyclopediaFlag.False,
            ArtworkGradeLevel.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
