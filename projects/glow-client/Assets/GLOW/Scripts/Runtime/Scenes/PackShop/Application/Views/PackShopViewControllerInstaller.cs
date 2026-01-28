using GLOW.Scenes.PackShop.Domain.UseCase;
using GLOW.Scenes.PackShop.Presentation.Presenters;
using GLOW.Scenes.PackShop.Presentation.Views;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.PackShop.Application.Views
{
    public class PackShopViewControllerInstaller : Installer
    {
        [Inject] PackShopViewController.Argument Argument;
        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();

            Container.Bind<GetPackProductListUseCase>().AsCached();
            Container.Bind<BuyPackShopProductUseCase>().AsCached();
            Container.Bind<GetPackContentItemUseCase>().AsCached();
            Container.Bind<GetRemainCountdownTimeUseCase>().AsCached();
            Container.Bind<SavePackProductDisplayedFlagUseCase>().AsCached();

            Container.BindViewWithKernal<PackShopViewController>();
            Container.BindInterfacesTo<PackShopPresenter>().AsCached();
        }
    }
}
