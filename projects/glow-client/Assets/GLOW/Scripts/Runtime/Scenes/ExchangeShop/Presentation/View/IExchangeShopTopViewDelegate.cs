using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.ViewModels;
using UIKit;

namespace GLOW.Scenes.ExchangeShop.Presentation.View
{
    public interface IExchangeShopTopViewDelegate
    {
        void OnViewDidLoad();
        void ShowTradeConfirmView(
            MasterDataId mstExchangeId,
            MasterDataId mstLineupId,
            PlayerResourceIconViewModel iconViewModel,
            UIIndexPath indexPath);
        void OnItemIconButtonTapped(PlayerResourceIconViewModel iconViewModel);
        void OnBackButtonTapped();
    }
}
