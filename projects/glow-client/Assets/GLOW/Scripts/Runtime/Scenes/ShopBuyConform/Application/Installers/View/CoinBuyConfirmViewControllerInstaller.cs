using GLOW.Scenes.ShopBuyConform.Domain.UseCase;
using GLOW.Scenes.ShopBuyConform.Presentation.Presenter;
using GLOW.Scenes.ShopBuyConform.Presentation.View;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.ShopBuyConform.Application.Installers.View
{
    public class CoinBuyConfirmViewControllerInstaller : Installer
    {
        [Inject] CoinBuyConfirmViewController.Argument Argument { get; }
        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<CoinBuyConfirmViewController>();
            Container.BindInterfacesTo<CoinBuyConfirmPresenter>().AsCached();
            
            Container.Bind<CurrentPlayerResourceInfoUseCase>().AsCached();
        }
    }
}