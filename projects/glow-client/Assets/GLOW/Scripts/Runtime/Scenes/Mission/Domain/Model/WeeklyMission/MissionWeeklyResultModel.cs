using System.Collections.Generic;
using GLOW.Scenes.Mission.Domain.Model.BonusPointMission;

namespace GLOW.Scenes.Mission.Domain.Model.WeeklyMission
{
    public record MissionWeeklyResultModel(
        MissionBonusPointResultModel BonusPointResultModel,
        IReadOnlyList<MissionWeeklyCellModel> MissionWeeklyModels)
    {
        public static MissionWeeklyResultModel Empty { get; } = new(
            MissionBonusPointResultModel.Empty,
            new List<MissionWeeklyCellModel>());
    }

}