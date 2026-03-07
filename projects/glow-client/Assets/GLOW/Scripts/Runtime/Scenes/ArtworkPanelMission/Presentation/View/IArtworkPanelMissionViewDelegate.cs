using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.ArtworkPanelMission.Presentation.View
{
    public interface IArtworkPanelMissionViewDelegate
    {
        void OnViewDidLoad();
        void OnCloseButtonTapped();
        void OnBulkReceiveButtonTapped();
        void OnReceiveButtonTapped(MasterDataId mstMissionId);
        void OnChallengeButtonTapped(DestinationScene destinationScene);
        void OnRewardIconTapped(PlayerResourceIconViewModel viewModel);
        void OnArtworkIconTapped(PlayerResourceIconViewModel viewModel);
        void OnSkipButtonTapped();
    }
}