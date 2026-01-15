using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.Home.Domain.Constants;

namespace GLOW.Scenes.ShopTab.Presentation.View
{
    public interface IShopTabViewDelegate
    {
        void OnViewDidLoad();
        void OnTabTapped(ShopContentTypes shopContentTypes);
        void OnChangeShopContent(ShopContentTypes shopContentTypes, MasterDataId mstPackId);
        void ShowSpecificCommerce();
        void ShowFundsSettlement();
        void UpdateShopTabBadge(bool isCheckOnlyAdvOrFree);
        void UpdatePackTabBadge();
        void UpdatePassTabBadge();
        void OnViewWillAppear();
    }
}
