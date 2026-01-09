using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record EventAssetKey(ObscuredString Value)
    {
        public static EventAssetKey Empty { get; } = new EventAssetKey(string.Empty);

        public bool IsEmpty() => ReferenceEquals(this, Empty);

    };
}
