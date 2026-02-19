using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;

namespace GLOW.Core.Domain.Models
{
    public record UserShopItemModel(
        MasterDataId MstShopItemId, 
        ShopItemTradeCount TradeCount, 
        ShopItemTradeCount TradeTotalCount)
    {
        public static UserShopItemModel Empty { get; } = new(
            MasterDataId.Empty, 
            ShopItemTradeCount.Empty, 
            ShopItemTradeCount.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
