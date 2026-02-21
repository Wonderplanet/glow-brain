using GLOW.Scenes.BeginnerMission.Domain.Model;
using GLOW.Scenes.Mission.Domain.Model.AchievementMission;

namespace GLOW.Scenes.Mission.Domain.Model
{
    public record MissionClearOnCallUseCaseModel(
        MissionAchievementResultModel MissionAchievementResultModel,
        MissionBeginnerResultModel MissionBeginnerResultModel);
}