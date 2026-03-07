using GLOW.Scenes.ArtworkPanelMission.Domain.UseCase;
using GLOW.Scenes.ArtworkPanelMission.Presentation.Presenter;
using GLOW.Scenes.ArtworkPanelMission.Presentation.View;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.ArtworkPanelMission.Application.Installers
{
    public class ArtworkPanelMissionViewControllerInstaller : Installer
    {
        [Inject] ArtworkPanelMissionViewController.Argument Argument { get; }
        public override void InstallBindings()
        {
            Container.BindInstance(Argument);
            Container.BindViewWithKernal<ArtworkPanelMissionViewController>();
            Container.BindInterfacesTo<ArtworkPanelMissionPresenter>().AsCached();

            Container.Bind<ReceiveArtworkPanelMissionRewardUseCase>().AsCached();
            Container.Bind<ShowReceivedArtworkPanelUseCase>().AsCached();
        }
    }
}