using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pass;
using GLOW.Core.Domain.ValueObjects.Shop;

namespace GLOW.Core.Domain.Models
{
    public record MstShopPassModel(
        MasterDataId MstShopPassId,
        MasterDataId OprProductId,
        ShopProductId ShopProductId,
        PassProductName PassProductName,
        ShopPassCellColor ShopPassCellColor,
        DisplayExpirationFlag IsDisplayExpiration,
        PassDurationDay PassDurationDays,
        PassAssetKey PassAssetKey,
        ProductPrice ProductPrice,
        PassStartAt PassStartAt,
        PassEndAt PassEndAt)
    {
        public static MstShopPassModel Empty { get; } = new(
            MasterDataId.Empty,
            MasterDataId.Empty,
            ShopProductId.Empty,
            PassProductName.Empty,
            ShopPassCellColor.Purple,
            DisplayExpirationFlag.False,
            PassDurationDay.Empty,
            PassAssetKey.Empty,
            ProductPrice.Empty,
            PassStartAt.Empty,
            PassEndAt.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}