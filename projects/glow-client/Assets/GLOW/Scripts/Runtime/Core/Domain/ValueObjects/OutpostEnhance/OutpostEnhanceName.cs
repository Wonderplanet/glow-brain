using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.OutpostEnhance
{
    public record OutpostEnhanceName(ObscuredString Value)
    {
        public static OutpostEnhanceName Empty { get; } = new(string.Empty);
    }
}
