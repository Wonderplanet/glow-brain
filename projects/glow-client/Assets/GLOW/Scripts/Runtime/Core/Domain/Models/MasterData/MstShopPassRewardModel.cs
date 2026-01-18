using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record MstShopPassRewardModel(
        MasterDataId Id,
        MasterDataId MstShopPassId,
        ShopPassRewardType ShopPassRewardType,
        ResourceType ResourceType,
        MasterDataId ResourceId,
        ObscuredPlayerResourceAmount ResourceAmount)
    {
        public static MstShopPassRewardModel Empty { get; } = new(
            MasterDataId.Empty,
            MasterDataId.Empty,
            ShopPassRewardType.Daily,
            ResourceType.Coin,
            MasterDataId.Empty,
            ObscuredPlayerResourceAmount.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}