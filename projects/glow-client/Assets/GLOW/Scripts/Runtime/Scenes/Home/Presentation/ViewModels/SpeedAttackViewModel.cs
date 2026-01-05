using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.Home.Presentation.ViewModels
{
    public record SpeedAttackViewModel(
        EventClearTimeMs ClearTimeMs,
        StageClearTime NextGoalTime)
    {
        public static SpeedAttackViewModel Empty { get; } = new(
            EventClearTimeMs.Empty,
            StageClearTime.Empty
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
