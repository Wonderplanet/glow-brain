using GLOW.Scenes.InGame.Presentation.Presenters;
using GLOW.Scenes.InGame.Presentation.Views;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.InGame.Application.Installers
{
    public class InGameStartAnimationViewControllerInstaller : Installer
    {
        [Inject] InGameStartAnimationViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<InGameStartAnimationViewController>();
            Container.BindInterfacesTo<InGameStartAnimationPresenter>().AsCached();
        }
    }
}
