using GLOW.Core.Domain.ValueObjects.Shop;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Pass
{
    public record PassProductName(ObscuredString Value)
    {
        public static PassProductName Empty { get; } = new(string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public override string ToString()
        {
            return Value.ToString();
        }

        public ProductName ToProductName()
        {
            return new ProductName(Value);
        }
    }
}
