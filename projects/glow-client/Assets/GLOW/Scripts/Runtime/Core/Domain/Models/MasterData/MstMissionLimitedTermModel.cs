using System;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Core.Domain.Models
{
    public record MstMissionLimitedTermModel(
        MasterDataId Id,
        MissionProgressGroupKey MissionProgressGroupKey,
        MissionCriterionType MissionCriterionType,
        CriterionValue CriterionValue,
        CriterionCount CriterionCount,
        MissionDescription MissionDescription,
        MissionCategory MissionCategory,
        MasterDataId MstMissionRewardGroupId,
        SortOrder SortOrder,
        DestinationScene DestinationScene,
        MasterDataId GroupId,
        UnlockOrder UnlockOrder,
        MissionStartDate StartDate,
        MissionEndDate EndDate)
    {
        public static MstMissionLimitedTermModel Empty => new MstMissionLimitedTermModel(
            MasterDataId.Empty,
            MissionProgressGroupKey.Empty,
            MissionCriterionType.MissionClearCount,
            CriterionValue.Empty,
            CriterionCount.Empty,
            MissionDescription.Empty,
            MissionCategory.AdventBattle,
            MasterDataId.Empty,
            SortOrder.Empty,
            DestinationScene.Empty,
            MasterDataId.Empty,
            UnlockOrder.Empty,
            new MissionStartDate(DateTimeOffset.MinValue),
            new MissionEndDate(DateTimeOffset.MaxValue));
    }
}