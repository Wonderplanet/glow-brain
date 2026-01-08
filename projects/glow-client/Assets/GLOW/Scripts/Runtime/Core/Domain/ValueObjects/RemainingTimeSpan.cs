using System;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record RemainingTimeSpan(ObscuredTimeSpan Value)
    {
        public static RemainingTimeSpan Empty { get; } = new (TimeSpan.Zero);
        
        public static RemainingTimeSpan Infinity { get; } = new (TimeSpan.MaxValue);

        public bool HasValue()
        {
            return Value != TimeSpan.Zero;
        }

        public bool IsMinus()
        {
            return Value < TimeSpan.Zero;
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
        
        public bool IsInfinity()
        {
            return ReferenceEquals(this, Infinity);
        }

        public bool IsZero()
        {
            return Value == TimeSpan.Zero;
        }
        
        public static bool operator >(RemainingTimeSpan a, RemainingTimeSpan b)
        {
            return a.Value > b.Value;
        }

        public static bool operator <(RemainingTimeSpan a, RemainingTimeSpan b)
        {
            return a.Value < b.Value;
        }


        public static RemainingTimeSpan Max(RemainingTimeSpan a, RemainingTimeSpan b)
        {
            return a.Value > b.Value ? a : b;
        }

        public static RemainingTimeSpan Min(RemainingTimeSpan a, RemainingTimeSpan b)
        {
            return a.Value < b.Value ? a : b;
        }

        public static RemainingTimeSpan Clamp(RemainingTimeSpan value, RemainingTimeSpan min, RemainingTimeSpan max)
        {
            if (value.Value < min.Value)
            {
                return min;
            }

            if (value.Value > max.Value)
            {
                return max;
            }

            return value;
        }

        public RemainingTimeSpan Subtract(TimeSpan timeSpan)
        {
            return new RemainingTimeSpan(Value.Subtract(timeSpan));
        }
    }
}
