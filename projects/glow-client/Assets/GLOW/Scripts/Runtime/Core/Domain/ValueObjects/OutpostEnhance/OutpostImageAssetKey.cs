using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.OutpostEnhance
{
    public record OutpostImageAssetKey(ObscuredString Value)
    {
        public static OutpostImageAssetKey Empty { get; } = new("");
    }
}
