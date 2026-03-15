using GLOW.Debugs.Command.Domains.UseCase;
using GLOW.Debugs.Command.Presentations.Views.DebugStageDetailView;
using Zenject;
using UIKit.ZenjectBridge;
using GLOW.Scenes.DebugStageDetail.Domain;

namespace GLOW.Scenes.DebugStageDetail.Application
{
    public class DebugStageDetailViewInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<DebugStageDetailViewController>();
            Container.BindInterfacesTo<DebugStageDetailPresenter>().AsCached();
            Container.Bind<DebugStageDetailUseCase>().AsCached();
            Container.Bind<DebugStageSummaryUseCase>().AsCached();
            Container.Bind<PvpDebugStageDetailModelFactory>().AsCached();
            Container.Bind<AdventBattleDebugStageDetailModelFactory>().AsCached();
        }
    }
}
