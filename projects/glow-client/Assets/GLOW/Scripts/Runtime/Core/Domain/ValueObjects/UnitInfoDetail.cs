using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record UnitInfoDetail(ObscuredString Value)
    {
        public static UnitInfoDetail Empty { get; } = new (string.Empty);
    }
}
