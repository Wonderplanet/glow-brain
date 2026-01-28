using GLOW.Core.Data.Repositories;
using GLOW.Core.Data.Services;
using GLOW.Scenes.AdventBattleMission.Domain.Applier;
using GLOW.Scenes.AdventBattleMission.Domain.Evaluator;
using GLOW.Scenes.AdventBattleMission.Domain.UseCase;
using GLOW.Scenes.AdventBattleMission.Presentation.Presenter;
using GLOW.Scenes.AdventBattleMission.Presentation.View;
using GLOW.Scenes.Mission.Domain.UseCase;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.AdventBattleMission.Application.View
{
    public class AdventBattleMissionViewControllerInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<AdventBattleMissionViewController>();
            Container.BindInterfacesTo<AdventBattleMissionPresenter>().AsCached();
            
            Container.BindInterfacesTo<MissionOfOfAdventBattleRepository>().AsCached();
            Container.BindInterfacesTo<AdventBattleMissionReceivedRewardApplier>().AsCached();
            Container.BindInterfacesTo<AdventBattleMissionReceivedStatusApplier>().AsCached();
            Container.BindInterfacesTo<AdventBattleDateTimeEvaluator>().AsCached();
            Container.Bind<ShowAdventBattleMissionListUseCase>().AsCached();
            Container.Bind<BulkReceiveAdventBattleMissionRewardUseCase>().AsCached();
            Container.Bind<ReceiveAdventBattleMissionRewardUseCase>().AsCached();
        }
    }
}
