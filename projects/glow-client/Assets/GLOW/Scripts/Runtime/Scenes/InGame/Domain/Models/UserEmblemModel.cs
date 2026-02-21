using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record UserEmblemModel(MasterDataId MstEmblemId, NewEncyclopediaFlag IsNewEncyclopedia)
    {
        public static UserEmblemModel Empty { get; } = new (MasterDataId.Empty, NewEncyclopediaFlag.False);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
