using GLOW.Scenes.Shop.Presentation.ViewModel;
using UIKit;

namespace GLOW.Scenes.Shop.Presentation.View
{
    public interface IShopCollectionViewDelegate
    {
        void ViewWillAppear();
        void ViewDidDisappear();
        void ShowShopProductInfo(ShopProductCellViewModel model);
        void OnItemIconTapped(ShopProductCellViewModel model);
        void OnPurchaseButtonTapped(ShopProductCellViewModel model, UIIndexPath indexPath);
        void OnPurchaseHistoryButtonTapped();
    }
}
