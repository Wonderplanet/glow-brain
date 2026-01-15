using GLOW.Scenes.Shop.Presentation.ViewModel;
using UIKit;

namespace GLOW.Scenes.BattleResult.Presentation.Views
{
    public interface IDiamondPurchaseViewDelegate
    {
        void OnViewDidLoad();
        void OnViewDidAppear();
        void OnViewDidUnload();
        void OnItemIconSelected(ShopProductCellViewModel shopProductCellViewModel);
        void OnProductInfoSelected(ShopProductCellViewModel shopProductCellViewModel);
        void OnPurchaseButtonTapped(
            ShopProductCellViewModel shopProductCellViewModel,
            UIIndexPath indexPath);
        void ShowDiamondPurchaseHistory();
        void OnSpecificCommerceSelected();
        void OnFundsSettlementSelected();
        void OnCloseSelected();
    }
}
