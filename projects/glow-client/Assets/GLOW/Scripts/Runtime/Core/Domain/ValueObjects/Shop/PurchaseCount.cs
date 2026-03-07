using UnityEngine;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Shop
{
    public record PurchaseCount(ObscuredInt Value)
    {
        public static PurchaseCount Empty { get; } = new(-1);
        public static PurchaseCount Zero { get; } = new(0);
        public static PurchaseCount Infinity { get; } = new(-2);

        public static PurchaseCount operator -(PurchaseCount a, PurchaseCount b)
        {
            return new(Mathf.Max(a.Value - b.Value, 0));
        }

        public static PurchaseCount operator -(PurchaseCount a, int b)
        {
            return new(Mathf.Max(a.Value - b, 0));
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public bool IsInfinity()
        {
            return ReferenceEquals(this, Infinity);
        }
    }
}
