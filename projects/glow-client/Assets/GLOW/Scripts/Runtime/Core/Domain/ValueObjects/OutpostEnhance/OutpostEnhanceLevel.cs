using System;
using System.Globalization;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.OutpostEnhance
{
    public record OutpostEnhanceLevel(ObscuredInt Value) : IComparable
    {
        public static OutpostEnhanceLevel Empty { get; } = new (0);
        public static OutpostEnhanceLevel One { get; } = new (1);

        public override string ToString()
        {
            return Value.ToString("N0", CultureInfo.InvariantCulture);
        }

        public string ToStringWithPrefixLv()
        {
            return "Lv." + Value.ToString("N0", CultureInfo.InvariantCulture);
        }

        public int CompareTo(object obj)
        {
            if (obj is OutpostEnhanceLevel other)
            {
                return Value.CompareTo(other.Value);
            }

            return 1;
        }

        public static bool operator < (OutpostEnhanceLevel left, OutpostEnhanceLevel right)
        {
            return left.CompareTo(right) < 0;
        }

        public static bool operator > (OutpostEnhanceLevel left, OutpostEnhanceLevel right)
        {
            return left.CompareTo(right) > 0;
        }

        public static bool operator <= (OutpostEnhanceLevel left, OutpostEnhanceLevel right)
        {
            return left.CompareTo(right) <= 0;
        }

        public static bool operator >=(OutpostEnhanceLevel left, OutpostEnhanceLevel right)
        {
            return left.CompareTo(right) >= 0;
        }

        public static OutpostEnhanceLevel operator + (OutpostEnhanceLevel left, int right)
        {
            return new OutpostEnhanceLevel(left.Value + right);
        }

        public static OutpostEnhanceLevel operator - (OutpostEnhanceLevel left, int right)
        {
            return new OutpostEnhanceLevel(left.Value - right);
        }
    }
}
