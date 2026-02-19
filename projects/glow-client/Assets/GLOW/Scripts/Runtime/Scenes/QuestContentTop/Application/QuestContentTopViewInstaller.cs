using Zenject;
using UIKit.ZenjectBridge;
using GLOW.Scenes.QuestContentTop.Presentation;
using GLOW.Scenes.QuestContentTop.Domain;
using GLOW.Scenes.QuestContentTop.Domain.Factory;

namespace GLOW.Scenes.QuestContentTop.Application
{
    public class QuestContentTopViewInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<QuestContentTopViewController>();
            Container.BindInterfacesTo<QuestContentTopPresenter>().AsCached();
            Container.Bind<QuestContentTopUseCase>().AsCached();
            Container.Bind<GetRecentAdventBattleRankingUseCase>().AsCached();
            Container.Bind<CheckOpenAdventBattleUseCase>().AsCached();
            Container.Bind<UpdateBadgeForContentTopUseCase>().AsCached();
            Container.BindInterfacesTo<QuestContentTopModelFactory>().AsCached();
            Container.BindInterfacesTo<QuestContentTopPvpModelFactory>().AsCached();
            Container.BindInterfacesTo<PvpChallengeStatusFactory>().AsCached();
        }
    }
}
