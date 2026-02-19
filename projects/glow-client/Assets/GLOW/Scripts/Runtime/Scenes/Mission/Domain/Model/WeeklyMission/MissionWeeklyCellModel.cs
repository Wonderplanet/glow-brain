using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Scenes.Mission.Domain.Model.WeeklyMission
{
    public record MissionWeeklyCellModel(
        MasterDataId MissionWeeklyId,
        MissionType MissionType,
        MissionStatus MissionStatus,
        MissionProgress MissionProgress,
        CriterionCount CriterionCount,
        BonusPoint BonusPoint,
        MissionDescription MissionDescription,
        SortOrder SortOrder,
        DestinationScene DestinationScene);
}