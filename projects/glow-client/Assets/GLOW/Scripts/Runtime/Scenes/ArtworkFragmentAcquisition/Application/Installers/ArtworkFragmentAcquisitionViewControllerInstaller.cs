using GLOW.Scenes.ArtworkFragmentAcquisition.Presentation.Presenters;
using GLOW.Scenes.ArtworkFragmentAcquisition.Presentation.Views;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.ArtworkFragmentAcquisition.Application.Installers
{
    public class ArtworkFragmentAcquisitionViewControllerInstaller : Installer
    {
        [Inject] ArtworkFragmentAcquisitionViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<ArtworkFragmentAcquisitionViewController>();
            Container.BindInterfacesTo<ArtworkFragmentAcquisitionPresenter>().AsCached();
        }
    }
}
