using System;
using UnityEngine;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Shop
{
    public record PurchasableCount(ObscuredInt Value)
    {
        public static PurchasableCount Empty { get; } = new(0);
        public static PurchasableCount Infinity { get; } = new(-1);
        public static PurchasableCount Zero { get; } = new(0);

        public static PurchasableCount operator -(PurchasableCount a, ShopItemTradeCount b)
        {
            if (b.Value < 0) throw new Exception("b.Value is minus number.");
            if (a.IsInfinity()) return Infinity;

            return new PurchasableCount(Mathf.Max(a.Value - b.Value, 0));
        }

        public static PurchasableCount operator -(PurchasableCount a, PurchaseCount b)
        {
            if (b.Value < 0) throw new Exception("b.Value is minus number.");
            if (a.IsInfinity()) return Infinity;

            return new PurchasableCount(Mathf.Max(a.Value - b.Value, 0));
        }

        public static PurchasableCount operator -(PurchasableCount a, int b)
        {
            if (b < 0) throw new Exception("b is minus number.");
            if (a.IsInfinity()) return Infinity;

            return new PurchasableCount(Mathf.Max(a.Value - b, 0));
        }

        public static bool operator > (PurchasableCount a, int b)
        {
            return a.Value > b;
        }

        public static bool operator <(PurchasableCount a, int b)
        {
            return a.Value < b;
        }

        public static bool operator > (PurchasableCount a, ShopItemTradeCount b)
        {
            return a.Value > b.Value;
        }

        public static bool operator <(PurchasableCount a, ShopItemTradeCount b)
        {
            return a.Value < b.Value;
        }

        public static bool operator > (PurchasableCount a, PurchaseCount b)
        {
            return a.Value > b.Value;
        }

        public static bool operator <(PurchasableCount a, PurchaseCount b)
        {
            return a.Value < b.Value;
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public bool IsZero()
        {
            return Value == 0;
        }

        public bool IsInfinity()
        {
            return Value < 0;
        }

        public bool IsPurchasable()
        {
            return IsInfinity() || 0 < Value;
        }

        public override string ToString()
        {
            return Value.ToString();
        }
    }
}
