using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Scenes.Shop.Domain.ValueObjects
{
    public record OverShopPurchaseLimitFlag(ObscuredBool Value)
    {
        public static OverShopPurchaseLimitFlag False { get; } = new OverShopPurchaseLimitFlag(false);
        public static OverShopPurchaseLimitFlag True { get; } = new OverShopPurchaseLimitFlag(true);

        public static implicit operator bool(OverShopPurchaseLimitFlag flag)
        {
            return flag.Value;
        }
    };
}
