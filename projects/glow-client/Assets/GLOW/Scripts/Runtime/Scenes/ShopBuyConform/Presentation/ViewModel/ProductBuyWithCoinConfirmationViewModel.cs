using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.ShopBuyConform.Presentation.ViewModel
{
    public record ProductBuyWithCoinConfirmationViewModel(
            ProductName ProductName,
            CostAmount CostAmount,
            Coin CurrentCoin,
            Coin AfterCoin,
            PlayerResourceIconViewModel PlayerResourceIconViewModel,
            IsFirstTimeFreeDisplay IsFirstTimeFreeDisplay);
}
