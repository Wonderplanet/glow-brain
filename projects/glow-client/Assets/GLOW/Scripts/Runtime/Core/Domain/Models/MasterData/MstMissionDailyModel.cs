using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Core.Domain.Models
{
    public record MstMissionDailyModel(
        MasterDataId Id,
        MissionCriterionType CriterionType,
        CriterionValue CriterionValue,
        CriterionCount CriterionCount,
        MissionDescription MissionDescription,
        GroupKey GroupKey,
        BonusPoint BonusPoint,
        MasterDataId MstMissionRewardGroupId,
        SortOrder SortOrder,
        DestinationScene DestinationScene)
    {
        public static MstMissionDailyModel Empty { get; } = new MstMissionDailyModel(
            MasterDataId.Empty,
            MissionCriterionType.LoginCount,
            CriterionValue.Empty,
            CriterionCount.Empty,
            MissionDescription.Empty,
            GroupKey.Empty,
            BonusPoint.Empty,
            MasterDataId.Empty,
            SortOrder.Empty,
            DestinationScene.Empty);
    }
}