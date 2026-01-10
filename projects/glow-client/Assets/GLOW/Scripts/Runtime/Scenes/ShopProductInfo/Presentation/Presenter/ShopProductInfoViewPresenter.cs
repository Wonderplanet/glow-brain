using GLOW.Scenes.ShopProductInfo.Presentation.View;
using WPFramework.Modules.Log;
using Zenject;

namespace GLOW.Scenes.ShopProductInfo.Presentation.Presenter
{
    public class ShopProductInfoViewPresenter : IShopProductInfoViewDelegate
    {
        [Inject] ShopProductInfoViewController ViewController { get; }
        
        public void OnViewDidLoad()
        {
            ApplicationLog.Log(nameof(ShopProductInfoViewPresenter), nameof(OnViewDidLoad));
        }

        public void OnViewDidUnload()
        {
            ApplicationLog.Log(nameof(ShopProductInfoViewPresenter), nameof(OnViewDidUnload));
        }

        public void OnCloseSelected()
        {
            ApplicationLog.Log(nameof(ShopProductInfoViewPresenter), nameof(OnCloseSelected));
            
            ViewController.Dismiss();
        }
    }
}