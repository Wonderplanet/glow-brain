using System;

namespace GLOW.Core.Domain.ValueObjects.InGame
{
    public record RushChargeCount(int Value) : IComparable
    {
        public static RushChargeCount Empty { get; } = new(0);

        public static RushChargeCount Zero { get; } = new(0);

        public int Value { get; } = Value > 0 ? Value : 0;

        public static RushChargeCount operator +(RushChargeCount a, RushChargeCount b)
        {
            return new RushChargeCount(a.Value + b.Value);
        }

        public static RushChargeCount operator -(RushChargeCount a, RushChargeCount b)
        {
            return new RushChargeCount(a.Value - b.Value);
        }

        public static RushChargeCount operator +(RushChargeCount a, int b)
        {
            return new RushChargeCount(a.Value + b);
        }

        public static RushChargeCount operator -(RushChargeCount a, int b)
        {
            return new RushChargeCount(a.Value - b);
        }

        public static bool operator <(RushChargeCount a, RushChargeCount b)
        {
            return a.Value < b.Value;
        }

        public static bool operator <=(RushChargeCount a, RushChargeCount b)
        {
            return a.Value <= b.Value;
        }

        public static bool operator >(RushChargeCount a, RushChargeCount b)
        {
            return a.Value > b.Value;
        }

        public static bool operator >=(RushChargeCount a, RushChargeCount b)
        {
            return a.Value >= b.Value;
        }

        public static bool operator ==(RushChargeCount a, int b)
        {
            return a.Value == b;
        }

        public static bool operator !=(RushChargeCount a, int b)
        {
            return !(a == b);
        }

        public static bool operator <(RushChargeCount a, int b)
        {
            return a.Value < b;
        }

        public static bool operator <=(RushChargeCount a, int b)
        {
            return a.Value <= b;
        }

        public static bool operator >(RushChargeCount a, int b)
        {
            return a.Value > b;
        }

        public static bool operator >=(RushChargeCount a, int b)
        {
            return a.Value >= b;
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public bool IsZero()
        {
            return Value == 0;
        }

        public int CompareTo(object obj)
        {
            if (obj is RushChargeCount other)
            {
                return Value.CompareTo(other.Value);
            }

            return 1;
        }
    }
}
