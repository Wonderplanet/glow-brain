using System;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record UnitRank(ObscuredInt Value) : IComparable
    {
        public static UnitRank Empty { get; } = new UnitRank(0);
        public static UnitRank Min { get; } = new UnitRank(0);

        public static bool operator <(UnitRank left, UnitRank right) => left.Value < right.Value;
        public static bool operator >(UnitRank left, UnitRank right) => left.Value > right.Value;
        public static bool operator <=(UnitRank left, UnitRank right) => left.Value <= right.Value;
        public static bool operator >=(UnitRank left, UnitRank right) => left.Value >= right.Value;
        public static UnitRank operator +(UnitRank left, int right) => new UnitRank(left.Value + right);

        public override string ToString()
        {
            return Value.ToString();
        }

        public string ToStringN0()
        {
            return Value.ToString("N0", null);
        }

        public UnitLevel ToRankReferenceLevel()
        {
            return new UnitLevel(Value * 10);
        }

        public int CompareTo(object obj)
        {
            if (obj is UnitRank rank)
            {
                return Value.CompareTo(rank.Value);
            }

            return -1;
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
