using System;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record UnitGrade(ObscuredInt Value) : IComparable
    {
        public static UnitGrade Empty { get; } = new(0);
        public static UnitGrade Minimum { get; } = new(1);

        public static bool operator > (UnitGrade left, int right) => left.Value > right;
        public static bool operator < (UnitGrade left, int right) => left.Value < right;
        public static bool operator >= (UnitGrade left, int right) => left.Value >= right;
        public static bool operator <= (UnitGrade left, int right) => left.Value <= right;
        public static bool operator > (UnitGrade left, UnitGrade right) => left.Value > right.Value;
        public static bool operator < (UnitGrade left, UnitGrade right) => left.Value < right.Value;
        public static bool operator >= (UnitGrade left, UnitGrade right) => left.Value >= right.Value;
        public static bool operator <= (UnitGrade left, UnitGrade right) => left.Value <= right.Value;
        public static UnitGrade operator +(UnitGrade left, UnitGrade right) => new(left.Value + right.Value);

        public int CompareTo(object obj)
        {
            if (obj is UnitGrade other)
            {
                return Value.CompareTo(other.Value);
            }

            return 1;
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
