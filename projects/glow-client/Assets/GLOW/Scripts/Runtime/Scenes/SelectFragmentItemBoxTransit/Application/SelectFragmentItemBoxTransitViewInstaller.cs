using GLOW.Scenes.ItemDetail.Presentation.Presenters;
using Zenject;
using UIKit.ZenjectBridge;
using GLOW.Scenes.SelectFragmentItemBoxTransit.Presentation;

namespace GLOW.Scenes.SelectFragmentItemBoxTransit.Application
{
    public class SelectFragmentItemBoxTransitViewInstaller : Installer
    {
        [Inject] SelectFragmentItemBoxTransitViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            Container.BindInstance(Argument);
            Container.BindViewWithKernal<SelectFragmentItemBoxTransitViewController>();
            Container.BindInterfacesTo<SelectFragmentItemBoxTransitPresenter>().AsCached();
            Container.Bind<ItemDetailTransitionWireFrame>().AsCached();
        }
    }
}
