using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record SpecialAttackInfoDescription(ObscuredString Value)
    {
        public static SpecialAttackInfoDescription Empty { get; } =  new SpecialAttackInfoDescription(string.Empty);
    };
}
