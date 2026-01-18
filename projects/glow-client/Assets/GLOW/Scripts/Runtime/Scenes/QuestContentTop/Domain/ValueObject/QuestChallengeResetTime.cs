using System;

namespace GLOW.Scenes.QuestContentTop.Domain.ValueObject
{
    public record QuestChallengeResetTime(TimeSpan Value)
    {
        public static QuestChallengeResetTime Empty { get; } = new(TimeSpan.Zero);
        public bool IsEmpty => ReferenceEquals(this, Empty);
    };
}
