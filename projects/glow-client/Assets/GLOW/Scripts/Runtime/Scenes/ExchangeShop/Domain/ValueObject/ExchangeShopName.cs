using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Scenes.ExchangeShop.Domain.ValueObject
{
    public record ExchangeShopName(ObscuredString Value)
    {
        public static ExchangeShopName Empty { get; } = new ExchangeShopName(string.Empty);
    }
}
