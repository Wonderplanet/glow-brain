using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Core.Domain.Models
{
    public record MstMissionBeginnerModel(
        MasterDataId Id,
        MissionCriterionType CriterionType,
        CriterionValue CriterionValue,
        CriterionCount CriterionCount,
        BeginnerMissionDayNumber UnlockDay,
        MissionDescription MissionDescription,
        GroupKey GroupKey,
        BonusPoint BonusPoint,
        MasterDataId MstMissionRewardGroupId,
        SortOrder SortOrder,
        DestinationScene DestinationScene)
    {
        public static MstMissionBeginnerModel Empty { get; } = new(
            MasterDataId.Empty,
            MissionCriterionType.LoginCount,
            CriterionValue.Empty,
            CriterionCount.Empty,
            BeginnerMissionDayNumber.Empty,
            MissionDescription.Empty,
            GroupKey.Empty,
            BonusPoint.Empty,
            MasterDataId.Empty,
            SortOrder.Empty,
            DestinationScene.Empty);
    }
}