using Zenject;
using UIKit.ZenjectBridge;
using GLOW.Scenes.PvpRanking.Presentation.Presenters;
using GLOW.Scenes.PvpRanking.Presentation.Views;

namespace GLOW.Scenes.PvpRanking.Application
{
    public class PvpRankingViewInstaller : Installer
    {
        [Inject] PvpRankingViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<PvpRankingViewController>();
            Container.BindInterfacesTo<PvpRankingPresenter>().AsCached();
        }
    }
}
