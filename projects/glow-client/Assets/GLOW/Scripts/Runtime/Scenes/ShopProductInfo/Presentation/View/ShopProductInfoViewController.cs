using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Core.Presentation.ViewModels;
using UIKit;
using Zenject;

namespace GLOW.Scenes.ShopProductInfo.Presentation.View
{
    public class ShopProductInfoViewController : UIViewController<ShopProductInfoView>
    {
        [Inject] IShopProductInfoViewDelegate ViewDelegate { get; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();

            ViewDelegate.OnViewDidLoad();
        }

        public override void ViewDidUnload()
        {
            base.ViewDidUnload();

            ViewDelegate.OnViewDidUnload();
        }
        
        public void SetShopItemViewModelTop(PlayerResourceIconViewModel model, ProductName name)
        {
            ActualView.SetupTopPlate(model, name);
        }

        public void SetShopItemViewModelBottom(PlayerResourceIconViewModel model, ProductName name)
        {
            ActualView.SetupBottomPlate(model, name);
        }

        [UIAction]
        void OnCloseButtonTapped()
        {
            ViewDelegate.OnCloseSelected();
        }
    }
}
