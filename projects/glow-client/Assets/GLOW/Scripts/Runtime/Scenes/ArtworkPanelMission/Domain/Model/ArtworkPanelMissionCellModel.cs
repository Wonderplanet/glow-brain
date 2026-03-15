using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Scenes.ArtworkPanelMission.Domain.Model
{
    public record ArtworkPanelMissionCellModel(
        MasterDataId MstMissionLimitedTermId,
        MissionType MissionType,
        MissionCategory MissionCategory,
        MissionStatus MissionStatus,
        MissionProgress MissionProgress,
        CriterionCount CriterionCount,
        PlayerResourceModel ArtworkFragmentPlayerResourceModel,
        PlayerResourceModel OtherRewardPlayerResourceModel,
        MissionDescription MissionDescription,
        SortOrder SortOrder,
        DestinationScene DestinationScene)
    {
        public static ArtworkPanelMissionCellModel Empty { get; } = new ArtworkPanelMissionCellModel(
            MasterDataId.Empty,
            MissionType.LimitedTerm,
            MissionCategory.ArtworkPanel,
            MissionStatus.Nothing,
            MissionProgress.Empty,
            CriterionCount.Empty,
            PlayerResourceModel.Empty,
            PlayerResourceModel.Empty,
            MissionDescription.Empty,
            SortOrder.Empty,
            DestinationScene.Empty);
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}