using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record UnitDescription(ObscuredString Value)
    {
        public static UnitDescription Empty { get; } = new (string.Empty);
    }
}
