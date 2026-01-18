using System;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;
using WPFramework.Presentation.Modules;

namespace GLOW.Core.Domain.ValueObjects
{
    public record UserLevel(ObscuredInt Value) : IComparable
    {
        public static UserLevel Empty { get; } = new(0);

        public static UserLevel operator +(UserLevel a, int b) => new(a.Value + b);
        public static UserLevel operator -(UserLevel a, int b) => new(a.Value - b);

        public static bool operator <(UserLevel a, UserLevel b)
        {
            return a.Value < b.Value;
        }

        public static bool operator <=(UserLevel a, UserLevel b)
        {
            return a.Value <= b.Value;
        }

        public static bool operator >(UserLevel a, UserLevel b)
        {
            return a.Value > b.Value;
        }

        public static bool operator >=(UserLevel a, UserLevel b)
        {
            return a.Value >= b.Value;
        }

        public static bool operator <(UserLevel a, int b)
        {
            return a.Value < b;
        }

        public static bool operator <=(UserLevel a, int b)
        {
            return a.Value <= b;
        }

        public static bool operator >(UserLevel a, int b)
        {
            return a.Value > b;
        }

        public static bool operator >=(UserLevel a, int b)
        {
            return a.Value >= b;
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public bool IsMinLevel()
        {
            return Value == 1;
        }

        public string ToStringAmount() { return AmountFormatter.FormatAmount(this.Value); }
        public int CompareTo(object obj)
        {
            if (obj is UserLevel other)
            {
                return Value.CompareTo(other.Value);
            }

            return -1;
        }
    }

}
