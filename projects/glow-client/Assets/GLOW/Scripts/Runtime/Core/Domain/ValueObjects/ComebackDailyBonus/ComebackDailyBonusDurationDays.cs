using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.ComebackDailyBonus
{
    public record ComebackDailyBonusDurationDays(ObscuredInt Value)
    {
        public static ComebackDailyBonusDurationDays Empty { get; } = new ComebackDailyBonusDurationDays(0);
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}