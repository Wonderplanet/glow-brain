using System.Collections.Generic;
using GLOW.Scenes.Mission.Domain.Model.BonusPointMission;

namespace GLOW.Scenes.Mission.Domain.Model.DailyBonusMission
{
    public record MissionDailyBonusResultModel(
        IReadOnlyList<MissionDailyBonusCellModel> MissionDailyBonusCellModels)
    {
        public static MissionDailyBonusResultModel Empty { get; } = new(
            new List<MissionDailyBonusCellModel>());
    }
}