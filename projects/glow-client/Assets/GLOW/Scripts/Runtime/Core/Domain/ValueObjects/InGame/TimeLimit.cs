using System;

namespace GLOW.Core.Domain.ValueObjects.InGame
{
    /// <summary> クエストの制限時間 </summary>
    public record TimeLimit(int Value) : IComparable
    {
        public static TimeLimit Empty { get; } = new TimeLimit(0);
        public static TimeLimit Zero { get; } = new TimeLimit(0);
        public static TimeLimit SpeedAttack { get; } = new TimeLimit(1000);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public bool IsZero()
        {
            return Value == 0;
        }

        public static bool operator < (TimeLimit a, TimeLimit b)
        {
            return a.Value < b.Value;
        }

        public static bool operator <= (TimeLimit a, TimeLimit b)
        {
            return a.Value <= b.Value;
        }

        public static bool operator > (TimeLimit a, TimeLimit b)
        {
            return a.Value > b.Value;
        }

        public static bool operator >= (TimeLimit a, TimeLimit b)
        {
            return a.Value >= b.Value;
        }

        public int CompareTo(object obj)
        {
            if (obj is TimeLimit other)
            {
                return Value.CompareTo(other.Value);
            }

            return 1;
        }
    }
}
