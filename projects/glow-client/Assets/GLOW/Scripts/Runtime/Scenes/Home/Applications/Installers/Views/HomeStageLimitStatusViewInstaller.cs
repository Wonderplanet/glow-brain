using GLOW.Scenes.Home.Presentation.Views.HomeStageLimitStatusView;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.Home.Applications.Installers.Views
{
    public class HomeStageLimitStatusViewInstaller : Installer
    {
        [Inject] HomeStageLimitStatusViewController.Argument Argument { get; }
        public override void InstallBindings()
        {
            Container.BindInstance(Argument);
            Container.BindViewWithKernal<HomeStageLimitStatusViewController>();

        }
    }
}
