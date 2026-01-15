using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.ExchangeShop.Domain.UseCase
{
    public record ExchangeShopTopAmountModel(ItemIconAssetPath ItemIconAssetPath, ItemAmount Amount);
}