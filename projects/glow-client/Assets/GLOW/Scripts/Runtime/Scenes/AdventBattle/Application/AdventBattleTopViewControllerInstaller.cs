using GLOW.Core.Data.Services;
using GLOW.Scenes.AdventBattle.Domain.Evaluator;
using GLOW.Scenes.AdventBattle.Domain.UseCase;
using GLOW.Scenes.AdventBattle.Presentation.Calculator;
using GLOW.Scenes.AdventBattle.Presentation.Presenter;
using GLOW.Scenes.AdventBattle.Presentation.View;
using GLOW.Scenes.AdventBattleRanking.Domain.UseCases;
using GLOW.Scenes.EnhanceQuestTop.Domain.UseCases;
using GLOW.Scenes.Home.Domain.UseCases;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.AdventBattle.Application
{
    public class AdventBattleTopViewControllerInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<AdventBattleTopViewController>();
            Container.BindInterfacesTo<AdventBattleTopPresenter>().AsCached();

            Container.BindInterfacesTo<AdventBattleHighScoreGaugeRateCalculator>().AsCached();
            Container.BindInterfacesTo<AdventBattleService>().AsCached();

            Container.Bind<FetchAdventBattleTopInfoUseCase>().AsCached();
            Container.Bind<SetPartyFormationEventBonusUseCase>().AsCached();
            Container.Bind<AdventBattleStartUseCase>().AsCached();
            Container.Bind<ReceiveAdventBattleScoreRewardsUseCase>().AsCached();
            Container.Bind<AdventBattleRankingUseCase>().AsCached();
            Container.Bind<ShowRaidAdventBattleTutorialDialogUseCase>().AsCached();
            Container.Bind<GetCurrentPartyNameUseCase>().AsCached();

            Container.BindInterfacesTo<ReceivableRaidTotalScoreRewardsEvaluator>().AsCached();
        }
    }
}
