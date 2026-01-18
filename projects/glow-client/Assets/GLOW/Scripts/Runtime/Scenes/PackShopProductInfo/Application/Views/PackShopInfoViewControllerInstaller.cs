using GLOW.Scenes.PackShopProductInfo.Domain.UseCase;
using GLOW.Scenes.PackShopProductInfo.Presentation.Presenters;
using GLOW.Scenes.PackShopProductInfo.Presentation.Views;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.PackShopProductInfo.Application.Views
{
    public class PackShopInfoViewControllerInstaller : Installer
    {
        [Inject] PackShopProductInfoViewController.Argument Argument { get; }
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<PackShopProductInfoViewController>();
            Container.BindInstance(Argument).AsCached();

            Container.Bind<GetPackProductInfoUseCase>().AsCached();
            Container.BindInterfacesTo<PackShopProductInfoPresenter>().AsCached();
        }
    }
}
