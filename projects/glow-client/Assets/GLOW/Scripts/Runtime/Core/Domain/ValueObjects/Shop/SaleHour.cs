using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Shop
{
    public record SaleHour(ObscuredInt Value)
    {
        public static SaleHour Empty = new SaleHour(-1);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
