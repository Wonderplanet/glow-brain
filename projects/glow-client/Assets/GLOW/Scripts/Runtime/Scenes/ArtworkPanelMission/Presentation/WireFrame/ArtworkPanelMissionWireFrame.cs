using GLOW.Scenes.ArtworkPanelMission.Presentation.View;
using GLOW.Scenes.ArtworkPanelMission.Presentation.ViewModel;
using GLOW.Scenes.Home.Presentation.Views;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.ArtworkPanelMission.Presentation.WireFrame
{
    public class ArtworkPanelMissionWireFrame : IArtworkPanelMissionWireFrame
    {
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] IHomeViewNavigation HomeViewNavigation { get; }
        
        public void ShowArtworkPanelMissionView(ArtworkPanelMissionViewModel viewModel)
        {
            var argument = new ArtworkPanelMissionViewController.Argument(viewModel);
            var controller = ViewFactory.Create<
                ArtworkPanelMissionViewController,
                ArtworkPanelMissionViewController.Argument>(argument);
            HomeViewNavigation.TryPush(controller, HomeContentDisplayType.BottomOverlap);
        }
    }
}