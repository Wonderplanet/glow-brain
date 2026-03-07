using System.Collections.Generic;
using GLOW.Scenes.Mission.Domain.Model.BonusPointMission;

namespace GLOW.Scenes.Mission.Domain.Model.DailyMission
{
    public record MissionDailyResultModel(
        MissionBonusPointResultModel BonusPointResultModel,
        IReadOnlyList<MissionDailyCellModel> MissionDailyModels)
    {
        public static MissionDailyResultModel Empty { get; } = new(
            MissionBonusPointResultModel.Empty,
            new List<MissionDailyCellModel>());
    }
}