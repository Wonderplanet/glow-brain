using GLOW.Scenes.ExchangeShop.Domain.UseCase;
using GLOW.Scenes.ExchangeShop.Presentation.Presenter;
using GLOW.Scenes.ExchangeShop.Presentation.View;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.ExchangeShop.Application.Installer.View
{
    public class ExchangeShopTopViewControllerInstaller : Zenject.Installer
    {
        [Inject] ExchangeShopTopViewController.Argument Argument { get; }
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<ExchangeShopTopViewController>();
            Container.BindInterfacesTo<ExchangeShopTopPresenter>().AsCached();

            Container.Bind<GetExchangeProductsUseCase>().AsCached();
            Container.Bind<CreateExchangeConfirmUseCase>().AsCached();

            Container.BindInstance(Argument).AsCached();
        }
    }
}
