using System;

namespace GLOW.Core.Domain.ValueObjects.Shop
{
    public record EndDateTime(DateTimeOffset Value)
    {
        public static EndDateTime Empty { get; } = new (DateTimeOffset.MinValue);
        public static EndDateTime Infinity { get; } = new (DateTimeOffset.MaxValue);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public bool IsInfinity()
        {
            return ReferenceEquals(this, Infinity);
        }

        public static TimeSpan operator -(EndDateTime endDateTime, DateTimeOffset dateTimeOffset)
        {
            return endDateTime.Value - dateTimeOffset;
        }
    }
}
