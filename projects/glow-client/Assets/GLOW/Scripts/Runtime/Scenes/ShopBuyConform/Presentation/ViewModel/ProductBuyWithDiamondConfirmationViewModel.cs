using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.ShopBuyConform.Presentation.ViewModel
{
    public record ProductBuyWithDiamondConfirmationViewModel(
        ProductName ProductName,
        CostAmount CostAmount,
        PaidDiamond CurrentPaidDiamond,
        PaidDiamond AfterPaidDiamond,
        FreeDiamond CurrentFreeDiamond,
        FreeDiamond AfterFreeDiamond,
        PlayerResourceIconViewModel PlayerResourceIconViewModel,
        IsFirstTimeFreeDisplay IsFirstTimeFreeDisplay);
}
