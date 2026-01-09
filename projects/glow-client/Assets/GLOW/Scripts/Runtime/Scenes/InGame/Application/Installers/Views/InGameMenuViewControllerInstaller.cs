using GLOW.Scenes.InGame.Presentation.Presenters;
using UIKit.ZenjectBridge;
using Zenject;
using GLOW.Scenes.InGame.Presentation.Views.InGameMenu;

namespace GLOW.Scenes.InGame.Application.Installers
{
    public class InGameMenuViewControllerInstaller : Installer
    {
        [Inject] InGameMenuViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<InGameMenuViewController>();
            Container.BindInterfacesTo<InGameMenuPresenter>().AsCached();
        }
    }
}
