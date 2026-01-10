using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Shop
{
    public record RawProductPriceText(ObscuredString Value)
    {
        public static RawProductPriceText Empty { get; } = new(string.Empty);

        public override string ToString()
        {
            return Value;
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
