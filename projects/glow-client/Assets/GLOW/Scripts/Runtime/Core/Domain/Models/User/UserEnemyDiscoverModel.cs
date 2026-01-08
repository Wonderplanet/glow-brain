using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record UserEnemyDiscoverModel(MasterDataId MstEnemyCharacterId, NewEncyclopediaFlag IsNewEncyclopedia)
    {
        public static UserEnemyDiscoverModel Empty { get; } = new(MasterDataId.Empty, NewEncyclopediaFlag.False);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
