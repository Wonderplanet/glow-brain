using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.ExchangeShop.Presentation.View
{
    public interface IFragmentTradeShopTopViewDelegate
    {
        void OnViewDidLoad();
        void ShowTradeConfirmView(MasterDataId itemMstId);
        void OnBackButtonTapped();
    }
}
