using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Scenes.BeginnerMission.Domain.Model;
using GLOW.Scenes.Mission.Domain.Model.AchievementMission;
using GLOW.Scenes.Mission.Domain.Model.DailyBonusMission;
using GLOW.Scenes.Mission.Domain.Model.DailyMission;
using GLOW.Scenes.Mission.Domain.Model.WeeklyMission;

namespace GLOW.Scenes.Mission.Domain.Model
{
    public record MissionFetchResultModel(
        MissionAchievementResultModel AchievementResultModel,
        MissionDailyBonusResultModel DailyBonusResultModel,
        MissionDailyResultModel DailyResultModel,
        MissionWeeklyResultModel WeeklyResultModel,
        MissionBeginnerResultModel BeginnerResultModel,
        BeginnerMissionDaysFromStart BeginnerMissionDaysFromStart)
    {
        public static MissionFetchResultModel Empty { get; } = new(
            MissionAchievementResultModel.Empty,
            MissionDailyBonusResultModel.Empty,
            MissionDailyResultModel.Empty,
            MissionWeeklyResultModel.Empty,
            MissionBeginnerResultModel.Empty,
            BeginnerMissionDaysFromStart.Empty);
    }
}