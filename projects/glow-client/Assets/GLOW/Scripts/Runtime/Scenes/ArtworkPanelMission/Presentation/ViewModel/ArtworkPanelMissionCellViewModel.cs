using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.ArtworkPanelMission.Presentation.ViewModel
{
    public record ArtworkPanelMissionCellViewModel(
        MasterDataId MstMissionLimitedTermId,
        MissionType MissionType,
        MissionStatus MissionStatus,
        MissionProgress MissionProgress,
        CriterionCount CriterionCount,
        PlayerResourceIconViewModel ArtworkFragmentPlayerResourceIconViewModel,
        PlayerResourceIconViewModel OtherRewardPlayerResourceIconViewModel,
        MissionDescription MissionDescription,
        DestinationScene DestinationScene)
    {
        public static ArtworkPanelMissionCellViewModel Empty { get; } = new ArtworkPanelMissionCellViewModel(
            MasterDataId.Empty,
            MissionType.LimitedTerm,
            MissionStatus.Nothing,
            MissionProgress.Empty,
            CriterionCount.Empty,
            PlayerResourceIconViewModel.Empty,
            PlayerResourceIconViewModel.Empty,
            MissionDescription.Empty,
            DestinationScene.Empty);
    }
}