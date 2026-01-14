using GLOW.Scenes.ShopBuyConform.Presentation.Presenter;
using GLOW.Scenes.ShopBuyConform.Presentation.View;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.ShopBuyConform.Application.Installers.View
{
    public class ExchangeConfirmViewControllerInstaller : Installer
    {
        [Inject] ExchangeConfirmViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<ExchangeConfirmViewController>();
            Container.BindInterfacesTo<ExchangeConfirmPresenter>().AsCached();
        }
    }
}
