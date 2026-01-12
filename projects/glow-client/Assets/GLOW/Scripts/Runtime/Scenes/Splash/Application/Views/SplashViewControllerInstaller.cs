using GLOW.Scenes.Splash.Presentation.Presenters;
using GLOW.Scenes.Splash.Presentation.Views;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.Splash.Application
{
    public class SplashViewControllerInstaller: Installer
    {
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<SplashViewController>();
            Container.BindInterfacesTo<SplashPresenter>().AsCached();
        }
    }
}