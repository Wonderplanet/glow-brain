using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ExchangeShop.Domain.ValueObject;

namespace GLOW.Scenes.ExchangeShop.Presentation.View
{
    public interface IExchangeContentTopViewDelegate
    {
        void OnViewDidLoad();
        bool IsOpeningExchangeShop(ExchangeShopEndTime endAt);
        void ShowExchangeShop(MasterDataId mstTradeShopId, ExchangeTradeType tradeType);
        void OnBackButtonTapped();
        void ShowBackToHomeMessage();
    }
}
