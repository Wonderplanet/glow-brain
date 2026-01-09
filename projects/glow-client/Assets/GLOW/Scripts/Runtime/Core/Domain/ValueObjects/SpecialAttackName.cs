using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record SpecialAttackName(ObscuredString Value)
    {
        public static SpecialAttackName Empty { get; } = new(string.Empty);
    }
}
