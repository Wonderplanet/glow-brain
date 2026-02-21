using System;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.TradeShop.Presentation.View
{
    public interface IExchangeConfirmViewDelegate
    {
        void OnViewDidLoad();
        void ShowItemDetail(PlayerResourceIconViewModel tradeIconViewModel);
        void OnTradeApply(Action onExchangeCompleted);
    }
}
