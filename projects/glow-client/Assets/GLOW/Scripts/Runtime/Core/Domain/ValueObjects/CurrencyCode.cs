using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record CurrencyCode(ObscuredString Value)
    {
        public static CurrencyCode Empty { get; } = new CurrencyCode(string.Empty);
        public static CurrencyCode JPY { get; } = new CurrencyCode("JPY");

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public override string ToString()
        {
            return Value;
        }
    }
}
