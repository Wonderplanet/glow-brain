using GLOW.Scenes.AdventBattleRanking.Domain.ModelFactories;
using Zenject;
using UIKit.ZenjectBridge;
using GLOW.Scenes.AdventBattleRanking.Domain.UseCases;
using GLOW.Scenes.AdventBattleRanking.Presentation.Presenters;
using GLOW.Scenes.AdventBattleRanking.Presentation.Views;

namespace GLOW.Scenes.AdventBattleRanking.Application
{
    public class AdventBattleRankingViewInstaller : Installer
    {
        [Inject] AdventBattleRankingViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<AdventBattleRankingViewController>();
            Container.BindInterfacesTo<AdventBattleRankingPresenter>().AsCached();
        }
    }
}
