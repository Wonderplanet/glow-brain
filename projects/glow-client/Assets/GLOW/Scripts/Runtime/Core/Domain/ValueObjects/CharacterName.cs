using Cysharp.Text;
using GLOW.Core.Domain.ValueObjects.Shop;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record CharacterName(ObscuredString Value)
    {
        public static CharacterName Empty { get; } = new CharacterName("");

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public ProductName ToProductName()
        {
            return new ProductName(Value);
        }

        public override string ToString()
        {
            return Value.ToString();
        }

        public PlayerResourceName ToPlayerResourceName()
        {
            return new PlayerResourceName(Value);
        }
    }
}
