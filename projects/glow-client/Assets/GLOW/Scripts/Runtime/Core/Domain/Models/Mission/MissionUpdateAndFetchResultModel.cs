using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Core.Domain.Models.Mission
{
    public record MissionUpdateAndFetchResultModel(
        IReadOnlyList<UserMissionAchievementModel> UserMissionAchievementModels,
        IReadOnlyList<UserMissionDailyBonusModel> UserMissionDailyBonusModels,
        IReadOnlyList<UserMissionDailyModel> UserMissionDailyModels,
        IReadOnlyList<UserMissionWeeklyModel> UserMissionWeeklyModels,
        IReadOnlyList<UserMissionBeginnerModel> UserMissionBeginnerModels,
        BeginnerMissionDaysFromStart BeginnerMissionDaysFromStart,
        IReadOnlyList<UserMissionBonusPointModel> UserMissionBonusPointModels)
    {
        public static MissionUpdateAndFetchResultModel Empty { get; } = new(
            new List<UserMissionAchievementModel>(),
            new List<UserMissionDailyBonusModel>(),
            new List<UserMissionDailyModel>(),
            new List<UserMissionWeeklyModel>(),
            new List<UserMissionBeginnerModel>(),
            BeginnerMissionDaysFromStart.Empty,
            new List<UserMissionBonusPointModel>()
        );
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}