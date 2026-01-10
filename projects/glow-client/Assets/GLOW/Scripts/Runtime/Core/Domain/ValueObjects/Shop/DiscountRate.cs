using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Shop
{
    public record DiscountRate(ObscuredInt Value)
    {
        public static DiscountRate Empty { get; } = new(0);

        public ObscuredInt Value { get; } = Value > 0 ? Value : 0;

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
