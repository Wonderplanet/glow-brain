using GLOW.Scenes.PvpOpponentDetail.Presentation;
using GLOW.Scenes.PvpOpponentDetail.Presentation.Views;
using Zenject;
using UIKit.ZenjectBridge;

namespace GLOW.Scenes.PvpOpponentDetail.Application
{
    public class PvpOpponentDetailViewInstaller : Installer
    {
        [Inject] PvpOpponentDetailViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<PvpOpponentDetailViewController>();
            Container.BindInterfacesTo<PvpOpponentDetailPresenter>().AsCached();
        }
    }
}
