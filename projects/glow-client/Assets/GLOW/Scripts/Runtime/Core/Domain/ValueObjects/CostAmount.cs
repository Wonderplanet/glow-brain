using System.Globalization;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Core.Domain.ValueObjects.Shop;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record CostAmount(ObscuredFloat Value)
    {
        public static CostAmount Empty { get; } = new(0);
        public static CostAmount Zero { get; } = new(0);

        public int ToInt { get; } = Value > 0 ? (int)Value : 0;

        public static bool operator <(CostAmount a, CostAmount b)
        {
            return a.Value < b.Value;
        }

        public static bool operator <=(CostAmount a, CostAmount b)
        {
            return a.Value <= b.Value;
        }

        public static bool operator >(CostAmount a, CostAmount b)
        {
            return a.Value > b.Value;
        }

        public static bool operator >=(CostAmount a, CostAmount b)
        {
            return a.Value >= b.Value;
        }
        
        public static bool operator <=(ItemAmount a, CostAmount b)
        {
            return a.Value <= b.Value;
        }
        
        public static bool operator >=(ItemAmount a, CostAmount b)
        {
            return a.Value >= b.Value;
        }
        
        public static CostAmount operator *(CostAmount a, GachaDrawCount b)
        {
            return new CostAmount((int)a.Value * b.Value);
        }

        public override string ToString()
        {
            return Value.ToString("N0", CultureInfo.InvariantCulture);
        }

        public TotalDiamond ToTotalDiamond()
        {
            return new((int)Value);
        }

        public ProductPrice ToProductPrice()
        {
            return new(Value);
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public bool IsZero()
        {
            return Value == 0;
        }

        public string ToRawPriceString()
        {
            return "¥" + Value.ToString("F0", CultureInfo.InvariantCulture);
        }
    }
}
