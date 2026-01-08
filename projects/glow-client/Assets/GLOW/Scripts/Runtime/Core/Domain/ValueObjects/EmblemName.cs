using GLOW.Core.Domain.ValueObjects.Shop;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record EmblemName(ObscuredString Value)
    {
        public static EmblemName Empty { get; } = new(string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public ProductName ToProductName()
        {
            return new(Value);
        }

        public PlayerResourceName ToPlayerResourceName()
        {
            return new PlayerResourceName(Value);
        }
    }
}
