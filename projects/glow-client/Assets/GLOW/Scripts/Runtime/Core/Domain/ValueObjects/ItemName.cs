using GLOW.Core.Domain.ValueObjects.Shop;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record ItemName(ObscuredString Value)
    {
        public static ItemName Empty { get; } = new ItemName("");

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
