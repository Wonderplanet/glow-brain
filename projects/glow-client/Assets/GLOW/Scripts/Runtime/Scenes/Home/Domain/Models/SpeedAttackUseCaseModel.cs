using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.Home.Domain.Models
{
    public record SpeedAttackUseCaseModel(
        EventClearTimeMs ClearTimeMs,
        StageClearTime NextGoalTime)
    {
        public static SpeedAttackUseCaseModel Empty { get; } = new (EventClearTimeMs.Empty, StageClearTime.Empty);

        public bool IsEmpty => ReferenceEquals(this, Empty);

        public bool IsRewardComplete() => NextGoalTime.IsEmpty();
    }
}
