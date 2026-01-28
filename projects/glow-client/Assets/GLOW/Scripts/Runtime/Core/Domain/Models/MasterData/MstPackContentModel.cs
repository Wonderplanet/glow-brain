using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;

namespace GLOW.Core.Domain.Models
{
    public record MstPackContentModel(
        MasterDataId MstPackId,
        ResourceType ResourceType,
        MasterDataId ResourceId,
        ObscuredPlayerResourceAmount ResourceAmount,
        BonusFlag IsBonus,
        SortOrder DisplayOrder)
    {
        public static MstPackContentModel Empty { get; } = new(
            MasterDataId.Empty,
            ResourceType.FreeDiamond,
            MasterDataId.Empty,
            ObscuredPlayerResourceAmount.Empty,
            BonusFlag.False,
            SortOrder.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
