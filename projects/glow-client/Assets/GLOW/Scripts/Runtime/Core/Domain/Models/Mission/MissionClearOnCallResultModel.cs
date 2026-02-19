using System.Collections.Generic;

namespace GLOW.Core.Domain.Models.Mission
{
    public record MissionClearOnCallResultModel(
        IReadOnlyList<UserMissionAchievementModel> UserMissionAchievementModels,
        IReadOnlyList<UserMissionBeginnerModel> UserMissionBeginnerModels)
    {
        public static MissionClearOnCallResultModel Empty { get; } = new(
            new List<UserMissionAchievementModel>(),
            new List<UserMissionBeginnerModel>());

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}