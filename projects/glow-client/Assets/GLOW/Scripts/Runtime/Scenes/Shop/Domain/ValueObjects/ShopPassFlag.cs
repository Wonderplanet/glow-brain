using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Scenes.Shop.Domain.ValueObjects
{
    public record ShopPassFlag(ObscuredBool Value)
    {
        public static ShopPassFlag False { get; } = new ShopPassFlag(false);
        public static ShopPassFlag True { get; } = new ShopPassFlag(true);

        public static implicit operator bool(ShopPassFlag flag)
        {
            return flag.Value;
        }
    };
}
