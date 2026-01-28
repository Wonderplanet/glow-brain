using GLOW.Scenes.PvpPreviousSeasonResult.Presentation.Presenters;
using GLOW.Scenes.PvpPreviousSeasonResult.Presentation.Views;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.PvpPreviousSeasonResult.Application.Installers
{
    public class PvpPreviousSessionResultViewControllerInstaller : Installer
    {
        [Inject] PvpPreviousSeasonResultViewController.Argument Argument { get; }
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<PvpPreviousSeasonResultViewController>();
            Container.BindInterfacesTo<PvpPreviousSeasonResultPresenter>().AsCached();
            Container.BindInstance(Argument).AsCached();
        }
    }
}
