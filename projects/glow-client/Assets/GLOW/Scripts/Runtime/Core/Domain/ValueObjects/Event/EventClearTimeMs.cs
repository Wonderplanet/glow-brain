using System;
using Cysharp.Text;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record EventClearTimeMs(ObscuredInt Value)
    {
        public static EventClearTimeMs Empty { get; } = new(0);

        public bool IsEmpty() => ReferenceEquals(this, Empty);

        public TimeSpan ToTimeSpan() => TimeSpan.FromMilliseconds(Value);

        public override string ToString()
        {
            var timeSpan = ToTimeSpan();
            return ZString.Format("{0:D3}.{1:D2}", (int)timeSpan.TotalSeconds, timeSpan.Milliseconds/10);
        }

        public static bool operator < (EventClearTimeMs left, EventClearTimeMs right)
        {
            return left.Value < right.Value;
        }

        public static bool operator > (EventClearTimeMs left, EventClearTimeMs right)
        {
            return left.Value > right.Value;
        }

        public static bool operator < (EventClearTimeMs left, StageClearTime right)
        {
            return left.ToTimeSpan() < right.Value;
        }

        public static bool operator > (EventClearTimeMs left, StageClearTime right)
        {
            return left.ToTimeSpan() > right.Value;
        }

        public static bool operator <= (EventClearTimeMs left, StageClearTime right)
        {
            return left.ToTimeSpan() <= right.Value;
        }

        public static bool operator >= (EventClearTimeMs left, StageClearTime right)
        {
            return left.ToTimeSpan() >= right.Value;
        }
    }
}
