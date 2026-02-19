using GLOW.Scenes.PvpNewSeasonStart.Presentation.Presenters;
using GLOW.Scenes.PvpNewSeasonStart.Presentation.Views;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.PvpNewSeasonStart.Application.Installers
{
    public class PvpNewSeasonStartViewControllerInstaller : Installer
    {
        [Inject] PvpNewSeasonStartViewController.Argument Argument { get; }
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<PvpNewSeasonStartViewController>();
            Container.BindInterfacesTo<PvpNewSeasonStartPresenter>().AsCached();
            Container.BindInstance(Argument).AsCached();
        }
    }
}
