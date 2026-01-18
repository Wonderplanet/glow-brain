using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Core.Domain.Models
{
    public record MstMissionAchievementModel(
        MasterDataId Id,
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
        MasterDataId GroupId,
        UnlockOrder UnlockOrder);
}
