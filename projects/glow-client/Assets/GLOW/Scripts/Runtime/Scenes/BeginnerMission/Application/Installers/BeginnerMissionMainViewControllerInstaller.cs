using GLOW.Modules.CommonReceiveView.Domain.UseCases;
using GLOW.Scenes.BeginnerMission.Domain.UseCase;
using GLOW.Scenes.BeginnerMission.Presentation.Presenter;
using GLOW.Scenes.BeginnerMission.Presentation.View;
using GLOW.Scenes.Mission.Domain.Creator;
using GLOW.Scenes.Mission.Domain.UseCase;
using UIKit.ZenjectBridge;
using WPFramework.Application.Modules;
using Zenject;

namespace GLOW.Scenes.BeginnerMission.Application.Installers
{
    public class BeginnerMissionMainViewControllerInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<BeginnerMissionMainViewController>();
            Container.BindInterfacesTo<BeginnerMissionMainPresenter>().AsCached();
            
            Container.BindInterfacesTo<ViewFactory>().AsCached();
            Container.BindViewFactoryInfo<BeginnerMissionContentViewController, BeginnerMissionContentViewControllerInstaller>();

            Container.Bind<BulkReceiveMissionRewardUseCase>().AsCached();
            Container.Bind<FetchMissionListUseCase>().AsCached();
            Container.Bind<ReceiveMissionRewardUseCase>().AsCached();
            Container.Bind<GetCommonReceiveItemUseCase>().AsCached();
            Container.Bind<CheckBeginnerMissionDayUnlockUseCase>().AsCached();
            Container.Bind<GetBeginnerMissionPromptPhraseUseCase>().AsCached();
            Container.Bind<ShowBonusPointMissionRewardReceivingUseCase>().AsCached();
            Container.Bind<BeginnerMissionScreenInteractionControl>().AsCached();

            Container.BindInterfacesTo<MissionResultModelFactory>().AsCached();
        }
    }
}
