using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Scenes.BeginnerMission.Domain.Model
{
    public record MissionBeginnerDayUnlockUseCaseModel(
        BeginnerMissionDaysFromStart PreviousDaysFromStart,
        BeginnerMissionDaysFromStart CurrentDaysFromStart)
    {
        public bool IsUnlockDay => PreviousDaysFromStart.Value < CurrentDaysFromStart.Value;
    }
}