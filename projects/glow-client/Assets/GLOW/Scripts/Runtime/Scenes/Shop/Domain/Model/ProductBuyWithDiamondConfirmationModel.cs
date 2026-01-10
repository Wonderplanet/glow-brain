using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.Shop.Domain.Model
{
    public record ProductBuyWithDiamondConfirmationModel(
        ConfirmationShopProductModel ProductModel,
        PaidDiamond BeforePaidDiamond,
        PaidDiamond AfterPaidDiamond,
        FreeDiamond BeforeFreeDiamond,
        FreeDiamond AfterFreeDiamond)
    {
        public static ProductBuyWithDiamondConfirmationModel Empty { get; } = new(
            ConfirmationShopProductModel.Empty,
            PaidDiamond.Empty,
            PaidDiamond.Empty,
            FreeDiamond.Empty,
            FreeDiamond.Empty);
    }
}
