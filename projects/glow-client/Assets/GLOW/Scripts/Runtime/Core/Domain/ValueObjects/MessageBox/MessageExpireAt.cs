using System;

namespace GLOW.Core.Domain.ValueObjects.MessageBox
{
    public record MessageExpireAt(DateTimeOffset Value)
    {
        public static MessageExpireAt Empty { get; } = new MessageExpireAt(DateTimeOffset.MinValue);

        public string ToShortDateString()
        {
            return Value.ToString("yyyy-MM-dd");
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public RemainingTimeSpan GetLimitedTimeSpan(DateTimeOffset nowTime)
        {
            return new RemainingTimeSpan(Value - nowTime);
        }
    }
}