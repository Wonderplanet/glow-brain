using GLOW.Scenes.BoxGachaResult.Presentation.Presenter;
using GLOW.Scenes.BoxGachaResult.Presentation.View;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.BoxGachaResult.Application.Installers
{
    public class BoxGachaResultViewControllerInstaller : Installer
    {
        [Inject] BoxGachaResultViewController.Argument Argument { get; }
        
        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<BoxGachaResultViewController>();
            Container.BindInterfacesTo<BoxGachaResultPresenter>().AsCached();
        }
    }
}