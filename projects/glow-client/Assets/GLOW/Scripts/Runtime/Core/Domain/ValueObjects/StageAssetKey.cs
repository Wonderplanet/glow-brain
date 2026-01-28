using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record StageAssetKey(ObscuredString Value)
    {
        public static StageAssetKey Empty { get; } = new(string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
        public InGameAssetKey ToInGameAssetKey()
        {
            return new InGameAssetKey(Value);
        }
    }

}
