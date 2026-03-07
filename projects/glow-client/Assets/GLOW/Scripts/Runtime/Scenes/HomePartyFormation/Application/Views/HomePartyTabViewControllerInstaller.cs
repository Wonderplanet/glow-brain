using GLOW.Scenes.ArtworkFormation.Application.Installers;
using GLOW.Scenes.ArtworkFormation.Presentation.Views;
using GLOW.Scenes.HomePartyFormation.Presentation.Presenters;
using GLOW.Scenes.HomePartyFormation.Presentation.Views;
using UIKit.ZenjectBridge;
using WPFramework.Application.Modules;
using Zenject;

namespace GLOW.Scenes.HomePartyFormation.Application.Views
{
    public class HomePartyTabViewControllerInstaller : Installer
    {
        [Inject] HomePartyTabViewController.Argument Argument { get; }
        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<HomePartyTabViewController>();
            Container.BindInterfacesTo<HomePartyTabPresenter>().AsCached();
        }
    }
}
