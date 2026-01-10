using GLOW.Scenes.AdventBattleRaidRankingResult.Presentation.Presenters;
using GLOW.Scenes.AdventBattleRaidRankingResult.Presentation.Views;
using Zenject;

namespace GLOW.Scenes.AdventBattleRaidRankingResult.Application
{
    public class AdventBattleRaidRankingResultViewInstaller : Installer
    {
        [Inject] AdventBattleRaidRankingResultViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.Bind<AdventBattleRaidRankingResultViewController>().AsCached();
            Container.BindInterfacesTo<AdventBattleRaidRankingResultPresenter>().AsCached();
        }
    }
}