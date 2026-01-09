using System;

namespace GLOW.Core.Domain.ValueObjects.Gacha
{
    public record GachaIntervalEndAt(DateTimeOffset Value)
    {
        public static GachaIntervalEndAt Empty { get; } = new(DateTimeOffset.MinValue);

        public DateTimeOffset Value { get; } = Value > DateTimeOffset.MinValue ? Value : DateTimeOffset.MinValue;

        public static bool operator <(GachaIntervalEndAt a, GachaIntervalEndAt b)
        {
            return a.Value < b.Value;
        }

        public static bool operator <=(GachaIntervalEndAt a, GachaIntervalEndAt b)
        {
            return a.Value <= b.Value;
        }

        public static bool operator >(GachaIntervalEndAt a, GachaIntervalEndAt b)
        {
            return a.Value > b.Value;
        }

        public static bool operator >=(GachaIntervalEndAt a, GachaIntervalEndAt b)
        {
            return a.Value >= b.Value;
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

    }
}
