using System;
using Cysharp.Text;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record StageClearTime(ObscuredTimeSpan Value) : IComparable
    {
        public static StageClearTime Empty { get; } = new (new ObscuredTimeSpan(TimeSpan.Zero));

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public override string ToString()
        {
            return ZString.Format("{0:D3}.{1:D2}", (int)Value.TotalSeconds, Value.Milliseconds/10);
        }

        public string ToStringSeconds()
        {
            return ZString.Format("{0}", Math.Ceiling(Value.TotalSeconds));
        }

        public int ToMilliSeconds()
        {
            return (int)Value.TotalMilliseconds;
        }

        public int CompareTo(object obj)
        {
            if (obj is StageClearTime other)
            {
                return Value.CompareTo(other.Value);
            }
            return -1;
        }

        public static bool operator < (StageClearTime left, EventClearTimeMs right)
        {
            return left.Value < right.ToTimeSpan();
        }

        public static bool operator > (StageClearTime left, EventClearTimeMs right)
        {
            return left.Value > right.ToTimeSpan();
        }

        public static bool operator <= (StageClearTime left, StageClearTime right)
        {
            return left.Value < right.Value;
        }

        public static bool operator >= (StageClearTime left, StageClearTime right)
        {
            return left.Value > right.Value;
        }

        public static implicit operator TimeSpan(StageClearTime stageClearTime)
        {
            return stageClearTime.Value;
        }

        public static implicit operator StageClearTime(TimeSpan timeSpan)
        {
            return new StageClearTime(new ObscuredTimeSpan(timeSpan));
        }
    }
}
