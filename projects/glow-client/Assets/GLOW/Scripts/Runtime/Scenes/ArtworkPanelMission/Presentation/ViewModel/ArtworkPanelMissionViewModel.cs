using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ArtworkFragment.Presentation.ViewModels;

namespace GLOW.Scenes.ArtworkPanelMission.Presentation.ViewModel
{
    public record ArtworkPanelMissionViewModel(
        MasterDataId MstArtworkPanelMissionId,
        MasterDataId MstEventId,
        ArtworkPanelViewModel ArtworkPanelViewModel,
        RemainingTimeSpan RemainingTimeSpan,
        ArtworkPanelMissionFetchResultViewModel ArtworkPanelMissionFetchResultViewModel)
    {
        public static ArtworkPanelMissionViewModel Empty { get; } = new(
            MasterDataId.Empty,
            MasterDataId.Empty,
            ArtworkPanelViewModel.Empty,
            RemainingTimeSpan.Empty,
            ArtworkPanelMissionFetchResultViewModel.Empty);
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}