using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Scenes.Mission.Domain.Model.DailyMission
{
    public record MissionDailyCellModel(
        MasterDataId MissionDailyId,
        MissionType MissionType,
        MissionStatus MissionStatus,
        MissionProgress MissionProgress,
        CriterionCount CriterionCount,
        BonusPoint BonusPoint,
        MissionDescription MissionDescription,
        SortOrder SortOrder,
        DestinationScene DestinationScene)
    {
        public static MissionDailyCellModel Empty { get; } = new(
            MasterDataId.Empty,
            MissionType.Daily,
            MissionStatus.Nothing,
            MissionProgress.Empty,
            CriterionCount.Empty,
            BonusPoint.Empty,
            MissionDescription.Empty,
            SortOrder.Empty,
            DestinationScene.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}