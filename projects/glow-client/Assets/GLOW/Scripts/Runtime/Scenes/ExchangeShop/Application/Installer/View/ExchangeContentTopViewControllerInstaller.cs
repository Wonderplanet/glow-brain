using GLOW.Scenes.ExchangeShop.Domain.UseCase;
using GLOW.Scenes.ExchangeShop.Presentation.Presenter;
using GLOW.Scenes.ExchangeShop.Presentation.View;
using UIKit.ZenjectBridge;

namespace GLOW.Scenes.ExchangeShop.Application.Installer.View
{
    internal sealed class ExchangeContentTopViewControllerInstaller : Zenject.Installer
    {
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<ExchangeContentTopViewController>();
            Container.BindInterfacesTo<ExchangeContentTopPresenter>().AsCached();

            Container.Bind<GetActiveExchangeContentUseCase>().AsCached();
        }
    }
}
