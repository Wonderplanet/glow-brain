using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record PartyFormationUnitSelectableFlag(ObscuredBool Value)
    {
        public static PartyFormationUnitSelectableFlag True { get; } = new(true);
        public static PartyFormationUnitSelectableFlag False { get; } = new(false);

        public static implicit operator bool(PartyFormationUnitSelectableFlag flag)
        {
            return flag.Value;
        }
    }
}
