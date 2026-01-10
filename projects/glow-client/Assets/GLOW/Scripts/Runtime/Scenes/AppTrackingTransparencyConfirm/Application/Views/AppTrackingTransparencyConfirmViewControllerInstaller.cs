using GLOW.Scenes.AppTrackingTransparencyConfirm.Domain.UseCases;
using GLOW.Scenes.AppTrackingTransparencyConfirm.Presentation.Presenters;
using GLOW.Scenes.AppTrackingTransparencyConfirm.Presentation.Views;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.AppTrackingTransparencyConfirm.Application.Views
{
    public class AppTrackingTransparencyConfirmViewControllerInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<AppTrackingTransparencyConfirmViewController>();
            Container.BindInterfacesTo<AppTrackingTransparencyConfirmPresenter>().AsCached();
            Container.Bind<SelectAppTrackingTransparencyUseCase>().AsCached();
        }
    }
}
