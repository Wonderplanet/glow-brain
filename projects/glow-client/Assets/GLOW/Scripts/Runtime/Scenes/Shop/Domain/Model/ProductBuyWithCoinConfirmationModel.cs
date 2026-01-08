using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.Shop.Domain.Model
{
    public record ProductBuyWithCoinConfirmationModel(
        ConfirmationShopProductModel ProductModel,
        Coin BeforeCoin,
        Coin AfterCoin)
    {
        public static ProductBuyWithCoinConfirmationModel Empty { get; } = new(
            ConfirmationShopProductModel.Empty,
            Coin.Empty,
            Coin.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
