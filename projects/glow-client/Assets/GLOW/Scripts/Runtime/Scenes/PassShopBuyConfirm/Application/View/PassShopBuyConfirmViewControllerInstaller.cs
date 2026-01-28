using GLOW.Scenes.PassShopBuyConfirm.Domain.UseCase;
using GLOW.Scenes.PassShopBuyConfirm.Presentation.Presenter;
using GLOW.Scenes.PassShopBuyConfirm.Presentation.View;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.PassShopBuyConfirm.Application.View
{
    public class PassShopBuyConfirmViewControllerInstaller : Installer
    {
        [Inject] PassShopBuyConfirmViewController.Argument Argument { get; }
        
        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<PassShopBuyConfirmViewController>();
            Container.BindInterfacesTo<PassShopBuyConfirmPresenter>().AsCached();

            Container.Bind<ShowPassBuyConfirmUseCase>().AsCached();
        }
    }
}