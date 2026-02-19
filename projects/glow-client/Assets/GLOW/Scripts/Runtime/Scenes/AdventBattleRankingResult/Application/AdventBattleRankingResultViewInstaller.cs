using GLOW.Scenes.AdventBattleResult.Domain.Factory;
using GLOW.Scenes.AdventBattleRankingResult.Presentation.Presenters;
using GLOW.Scenes.AdventBattleRankingResult.Presentation.Views;
using Zenject;

namespace GLOW.Scenes.AdventBattleRankingResult.Application
{
    public class AdventBattleRankingResultViewInstaller : Installer
    {
        [Inject] AdventBattleRankingResultViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.Bind<AdventBattleRankingResultViewController>().AsCached();
            Container.BindInterfacesTo<AdventBattleRankingResultPresenter>().AsCached();

            Container.Bind<AdventBattleResultScoreModelFactory>().AsCached();
        }
    }
}