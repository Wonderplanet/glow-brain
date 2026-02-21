using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Shop
{
    public record ShopItemTradeCount(ObscuredInt Value)
    {
        public static ShopItemTradeCount Empty { get; } = new(0);

        public static bool operator <(ShopItemTradeCount a, PurchasableCount b)
        {
            return a.Value < b.Value;
        }

        public static bool operator <=(ShopItemTradeCount a, PurchasableCount b)
        {
            return a.Value <= b.Value;
        }

        public static bool operator >(ShopItemTradeCount a, PurchasableCount b)
        {
            return a.Value > b.Value;
        }

        public static bool operator >=(ShopItemTradeCount a, PurchasableCount b)
        {
            return a.Value >= b.Value;
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