using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.InGame
{
    public record FontAssetPath(ObscuredString Value)
    {
        public static FontAssetPath Empty { get; } = new (string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public override string ToString()
        {
            return Value.ToString();
        }
    }
}
