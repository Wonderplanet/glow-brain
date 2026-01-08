using GLOW.Scenes.ShopBuyConform.Domain.UseCase;
using GLOW.Scenes.ShopBuyConform.Presentation.Presenter;
using GLOW.Scenes.ShopBuyConform.Presentation.View;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.ShopBuyConform.Application.Installers.View
{
    public class CashBuyConfirmViewControllerInstaller : Installer
    {
        [Inject] CashBuyConfirmViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<CashBuyConfirmViewController>();
            Container.BindInterfacesTo<CashBuyConfirmPresenter>().AsCached();
        }
    }
}
