using GLOW.Scenes.UnitReceive.Domain.UseCase;
using GLOW.Scenes.UnitReceive.Presentation.Presenter;
using GLOW.Scenes.UnitReceive.Presentation.View;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.UnitReceive.Application.View
{
    public class UnitReceiveViewControllerInstaller : Installer
    {
        [Inject] UnitReceiveViewController.Argument Argument { get; }
        
        public override void InstallBindings()
        {
            Container.BindInstance(Argument);
            
            Container.BindViewWithKernal<UnitReceiveViewController>();
            Container.BindInterfacesTo<UnitReceivePresenter>().AsCached();
        }
    }
}