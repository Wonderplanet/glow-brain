using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.OutpostEnhance
{
    public record OutpostEnhanceIconAssetKey(ObscuredString Value)
    {
        public static OutpostEnhanceIconAssetKey Empty { get; } = new(string.Empty);
    }
}
