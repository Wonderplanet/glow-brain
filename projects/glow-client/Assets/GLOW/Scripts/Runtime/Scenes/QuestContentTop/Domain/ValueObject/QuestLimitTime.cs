using System;

namespace GLOW.Scenes.QuestContentTop.Domain.ValueObject
{
    public record QuestLimitTime(TimeSpan Value)
    {
        public static QuestLimitTime Empty { get; } = new(TimeSpan.Zero);
        public bool IsEmpty => ReferenceEquals(this, Empty);
    }
}
