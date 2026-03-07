using System;

namespace GLOW.Core.Domain.ValueObjects.Gacha
{
    public record AdGachaResetRemainingTimeSpan(TimeSpan Value)
    {
        public TimeSpan Value { get; } = Value;
        public static AdGachaResetRemainingTimeSpan Zero { get; } = new(TimeSpan.Zero);

        public bool HasValue()
        {
            return Value != TimeSpan.Zero;
        }
    }
}
