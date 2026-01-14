using System;
using System.Globalization;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record UserExp(ObscuredLong Value) : IComparable<UserExp>
    {
        public static UserExp Empty { get; } = new (0);
        public static UserExp Zero { get; } = new (0);

        public static UserExp operator +(UserExp a, int b) => new(a.Value + b);
        public static UserExp operator +(UserExp a, UserExp b) => new(a.Value + b.Value);
        public static UserExp operator -(UserExp a, UserExp b) => new(a.Value - b.Value);
        public static float operator /(UserExp a, UserExp b) => (float)a.Value / b.Value;

        public static bool operator <(UserExp a, UserExp b)
        {
            return a.Value < b.Value;
        }

        public static bool operator <=(UserExp a, UserExp b)
        {
            return a.Value <= b.Value;
        }

        public static bool operator >(UserExp a, UserExp b)
        {
            return a.Value > b.Value;
        }

        public static bool operator >=(UserExp a, UserExp b)
        {
            return a.Value >= b.Value;
        }

        public static UserExp Min(UserExp a, UserExp b)
        {
            return a <= b ? a : b;
        }

        public int CompareTo(UserExp other)
        {
            return Value.CompareTo(other.Value);
        }

        public override string ToString()
        {
            return Value.ToString("N0", CultureInfo.InvariantCulture);
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public bool IsZero()
        {
            return Value == 0;
        }

        public PlayerResourceAmount ToPlayerResourceAmount()
        {
            return new PlayerResourceAmount(Convert.ToInt32(Value));
        }
    }
}
