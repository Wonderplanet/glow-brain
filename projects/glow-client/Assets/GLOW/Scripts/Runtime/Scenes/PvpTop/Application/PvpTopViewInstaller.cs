using GLOW.Modules.Tutorial.Application.Context;
using GLOW.Modules.Tutorial.Presentation.Sequence.FreePart;
using GLOW.Scenes.AdventBattleRanking.Domain.ModelFactories;
using GLOW.Scenes.Home.Domain.UseCases;
using GLOW.Scenes.PartyFormation.Domain.Evaluator;
using GLOW.Scenes.PvpRanking.Domain.ModelFactories;
using GLOW.Scenes.PvpRanking.Domain.UseCases;
using GLOW.Scenes.PvpTop.Domain.ModelFactories;
using Zenject;
using UIKit.ZenjectBridge;
using GLOW.Scenes.PvpTop.Presentation;
using GLOW.Scenes.PvpTop.Domain.UseCase;
using GLOW.Scenes.QuestContentTop.Domain.Factory;

namespace GLOW.Scenes.PvpTop.Application
{
    public class PvpTopViewInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<PvpTopViewController>();
            Container.BindInterfacesTo<PvpTopPresenter>().AsCached();
            Container.Bind<PvpTopUseCase>().AsCached();
            Container.Bind<PvpTopOpponentUseCase>().AsCached();
            Container.Bind<GetPvpTopRankingStateUseCase>().AsCached();
            Container.Bind<GetCurrentPartyNameUseCase>().AsCached();

            Container.BindInterfacesTo<PvpTopModelFactory>().AsCached();
            Container.BindInterfacesTo<PvpTopOpponentModelFactory>().AsCached();
            Container.BindInterfacesTo<PvpTopRankingStateFactory>().AsCached();
            Container.BindInterfacesTo<PvpTopUserStateFactory>().AsCached();
            Container.BindInterfacesTo<PvpChallengeStatusFactory>().AsCached();
            Container.BindInterfacesTo<PvpUserRankStatusFactory>().AsCached();
            Container.Bind<PvpRankingUseCase>().AsCached();
            Container.BindInterfacesTo<PvpRankingModelFactory>().AsCached();
            Container.BindInterfacesTo<InGameSpecialRuleUnitStatusEvaluator>().AsCached();

            Container.BindInterfacesTo<PvpOpponentRefreshCoolTimeFactory>().AsCached();
        }
    }
}
