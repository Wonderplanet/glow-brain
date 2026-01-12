using GLOW.Scenes.BeginnerMission.Presentation.Presenter;
using GLOW.Scenes.BeginnerMission.Presentation.View;
using GLOW.Scenes.Mission.Domain.UseCase;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.BeginnerMission.Application.Installers
{
    public class BeginnerMissionContentViewControllerInstaller : Installer

    {
        [Inject] BeginnerMissionContentViewController.Argument Argument { get; }
        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<BeginnerMissionContentViewController>();
            Container.BindInterfacesTo<BeginnerMissionContentPresenter>().AsCached();
            
            Container.Bind<ReceiveMissionRewardUseCase>().AsCached();
        }
    }
}