using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Constants.Shop;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.ShopBuyConform.Presentation.ViewModel
{
    public record ProductBuyWithCashConfirmationViewModel(
        MasterDataId OprProductId,
        ProductType ProductType,
        DisplayCostType DisplayCostType,
        ProductName ProductName,
        RawProductPriceText ProductPrice,
        PlayerResourceIconViewModel PlayerResourceIconViewModel,
        DiscountRate DiscountRate,
        IsFirstTimeFreeDisplay IsFirstTimeFreeDisplay);
}
