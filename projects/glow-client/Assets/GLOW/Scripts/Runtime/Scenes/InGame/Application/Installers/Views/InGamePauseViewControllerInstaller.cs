using UIKit.ZenjectBridge;
using Zenject;
using GLOW.Scenes.InGame.Presentation.Views.InGamePause;

namespace GLOW.Scenes.InGame.Application.Installers
{
    public class InGamePauseViewControllerInstaller : Installer
    {
        [Inject] InGamePauseViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<InGamePauseViewController>();
        }
    }
}
