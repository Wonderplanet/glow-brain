using System;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record UnitLevel(ObscuredInt Value) : IComparable
    {
        public static UnitLevel Empty { get; } = new(0);

        public static UnitLevel One { get; } = new(1);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public override string ToString()
        {
            return Value.ToString("N0", null);
        }

        public string ToStringWithPrefixLv()
        {
            return "Lv." + Value.ToString("N0", null);
        }

        public int CompareTo(object obj)
        {
            if (obj is UnitLevel other)
            {
                return Value.CompareTo(other.Value);
            }

            return 1;
        }

        public static bool operator < (UnitLevel left, UnitLevel right)
        {
            return left.CompareTo(right) < 0;
        }

        public static bool operator > (UnitLevel left, UnitLevel right)
        {
            return left.CompareTo(right) > 0;
        }

        public static bool operator <= (UnitLevel left, UnitLevel right)
        {
            return left.CompareTo(right) <= 0;
        }

        public static bool operator >=(UnitLevel left, UnitLevel right)
        {
            return left.CompareTo(right) >= 0;
        }

        public static UnitLevel operator + (UnitLevel left, UnitLevel right)
        {
            return new UnitLevel(left.Value + right.Value);
        }

        public static UnitLevel operator - (UnitLevel left, UnitLevel right)
        {
            return new UnitLevel(left.Value - right.Value);
        }

        public static UnitLevel operator + (UnitLevel left, int right)
        {
            return new UnitLevel(left.Value + right);
        }

        public static UnitLevel operator - (UnitLevel left, int right)
        {
            return new UnitLevel(left.Value - right);
        }
    }
}
