using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Core.Domain.Models
{
    public record MissionStatusModel(MissionAllCompleted BeginnerMissionAllCompleted)
    {
        public static MissionStatusModel Empty { get; } = new MissionStatusModel(MissionAllCompleted.DefaultSetting);
    }
}