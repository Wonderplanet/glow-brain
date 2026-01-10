using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record ShopProductId(ObscuredString Value)
    {
        public static ShopProductId Empty { get; } = new ShopProductId(string.Empty);

        public override string ToString()
        {
            return Value.ToString();
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
