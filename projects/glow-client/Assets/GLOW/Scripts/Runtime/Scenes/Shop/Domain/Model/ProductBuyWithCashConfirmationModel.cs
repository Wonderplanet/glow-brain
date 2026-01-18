namespace GLOW.Scenes.Shop.Domain.Model
{
    public record ProductBuyWithCashConfirmationModel(ConfirmationShopProductModel ProductModel)
    {
        public static ProductBuyWithCashConfirmationModel Empty { get; } = new(ConfirmationShopProductModel.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
