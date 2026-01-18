using System.Collections.Generic;

namespace GLOW.Scenes.Mission.Domain.Model.AchievementMission
{
    public record MissionAchievementResultModel(
        IReadOnlyList<MissionAchievementCellModel> AchievementCellModels)
    {
        public static MissionAchievementResultModel Empty { get; } = new(
            new List<MissionAchievementCellModel>());
    }
}