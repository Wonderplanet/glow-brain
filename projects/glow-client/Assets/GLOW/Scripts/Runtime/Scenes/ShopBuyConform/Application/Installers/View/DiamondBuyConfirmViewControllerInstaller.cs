using GLOW.Scenes.ShopBuyConform.Domain.UseCase;
using GLOW.Scenes.ShopBuyConform.Presentation.Presenter;
using GLOW.Scenes.ShopBuyConform.Presentation.View;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.ShopBuyConform.Application.Installers.View
{
    public class DiamondBuyConfirmViewControllerInstaller : Installer
    {
        [Inject] DiamondBuyConfirmViewController.Argument Argument { get; }
        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<DiamondBuyConfirmViewController>();
            Container.BindInterfacesTo<DiamondBuyConfirmPresenter>().AsCached();
            
            Container.Bind<CurrentPlayerResourceInfoUseCase>().AsCached();
        }
    }
}