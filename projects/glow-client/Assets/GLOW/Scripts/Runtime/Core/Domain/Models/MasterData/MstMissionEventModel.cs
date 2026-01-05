using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Core.Domain.Models
{
    public record MstMissionEventModel(
        MasterDataId Id,
        MasterDataId MstEventId,
        MissionCriterionType CriterionType,
        CriterionValue CriterionValue,
        CriterionCount CriterionCount,
        MissionDescription MissionDescription,
        MissionCriterionType? UnlockCriterionType,
        CriterionValue UnlockCriterionValue,
        CriterionCount UnlockCriterionCount,
        GroupKey GroupKey,
        MasterDataId MstMissionRewardGroupId,
        SortOrder SortOrder,
        DestinationScene DestinationScene,
        EventCategory EventCategory,
        MasterDataId GroupId,
        UnlockOrder UnlockOrder)
    {
        public static MstMissionEventModel Empty { get; } = new(
            MasterDataId.Empty,
            MasterDataId.Empty,
            MissionCriterionType.MissionClearCount,
            CriterionValue.Empty,
            CriterionCount.Empty,
            MissionDescription.Empty,
            null,
            CriterionValue.Empty,
            CriterionCount.Empty,
            GroupKey.Empty,
            MasterDataId.Empty,
            SortOrder.Empty,
            DestinationScene.Empty,
            EventCategory.None,
            MasterDataId.Empty,
            UnlockOrder.Empty);
    }
}