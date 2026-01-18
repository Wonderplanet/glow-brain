using System.Globalization;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Shop
{
    public record ProductPrice(ObscuredFloat Value)
    {
        public static ProductPrice Empty { get; } = new(0);

        public int ToInt { get; } = Value > 0 ? (int)Value : 0;

        public static bool operator <(ProductPrice a, ProductPrice b)
        {
            return a.Value < b.Value;
        }

        public static bool operator <=(ProductPrice a, ProductPrice b)
        {
            return a.Value <= b.Value;
        }

        public static bool operator >(ProductPrice a, ProductPrice b)
        {
            return a.Value > b.Value;
        }

        public static bool operator >=(ProductPrice a, ProductPrice b)
        {
            return a.Value >= b.Value;
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

        public CostAmount ToCostAmount()
        {
            return new CostAmount(Value);
        }
    }
}
