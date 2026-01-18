using GLOW.Scenes.FragmentProvisionRatio.Domain;
using GLOW.Scenes.FragmentProvisionRatio.Presentation;
using GLOW.Scenes.ItemDetail.Presentation.Presenters;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.FragmentProvisionRatio.Application
{
    public class FragmentProvisionRatioViewInstaller : Installer
    {
        [Inject] FragmentProvisionRatioViewController.Argument Argument { get; }
        public override void InstallBindings()
        {
            Container.BindInstance(Argument);
            Container.BindViewWithKernal<FragmentProvisionRatioViewController>();
            Container.BindInterfacesTo<FragmentProvisionRatioPresenter>().AsCached();
            Container.Bind<ItemDetailTransitionWireFrame>().AsCached();

            Container.Bind<FragmentProvisionRatioUseCase>().AsCached();
            Container.Bind<CheckTransitToShopUseCase>().AsCached();
        }
    }
}
