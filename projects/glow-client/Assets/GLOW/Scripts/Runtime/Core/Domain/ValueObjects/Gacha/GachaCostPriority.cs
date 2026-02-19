using System;
using System.Globalization;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Gacha
{
    public record GachaCostPriority(ObscuredInt Value): IComparable<GachaCostPriority>
    {
        public static GachaCostPriority Empty { get; } = new(-1);

        public ObscuredInt Value { get; } = Value > 0 ? Value : 0;

        public static bool operator <(GachaCostPriority a, GachaCostPriority b)
        {
            return a.Value < b.Value;
        }

        public static bool operator <=(GachaCostPriority a, GachaCostPriority b)
        {
            return a.Value <= b.Value;
        }

        public static bool operator >(GachaCostPriority a, GachaCostPriority b)
        {
            return a.Value > b.Value;
        }

        public static bool operator >=(GachaCostPriority a, GachaCostPriority b)
        {
            return a.Value >= b.Value;
        }

        int IComparable<GachaCostPriority>.CompareTo(GachaCostPriority other)
        {
            return this.Value.CompareTo(other.Value);
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
    }
}
