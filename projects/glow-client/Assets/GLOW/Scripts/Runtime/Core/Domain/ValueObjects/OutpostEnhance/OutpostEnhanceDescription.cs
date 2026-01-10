using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.OutpostEnhance
{
    public record OutpostEnhanceDescription(ObscuredString Value)
    {
        public static OutpostEnhanceDescription Empty { get; } = new("");
    }
}
