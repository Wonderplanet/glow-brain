using GLOW.Scenes.DiamondConsumeConfirm.Presentation.Presenters;
using GLOW.Scenes.DiamondConsumeConfirm.Presentation.Views;
using GLOW.Scenes.ShopBuyConform.Domain.UseCase;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.DiamondConsumeConfirm.Application.Views
{
    public class DiamondConsumeConfirmViewControllerInstaller : Installer
    {
        [Inject] DiamondConsumeConfirmViewController.Argument Argument { get; }
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<DiamondConsumeConfirmViewController>();

            Container.BindInterfacesTo<DiamondConsumeConfirmPresenter>().AsCached();
            Container.Bind<CurrentPlayerResourceInfoUseCase>().AsCached();
            Container.BindInstance(Argument).AsCached();
        }
    }
}
