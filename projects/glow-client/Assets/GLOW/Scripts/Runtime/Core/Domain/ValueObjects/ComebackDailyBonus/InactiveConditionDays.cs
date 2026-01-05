using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.ComebackDailyBonus
{
    public record InactiveConditionDays(ObscuredInt Value)
    {
        public static InactiveConditionDays Empty { get; } = new InactiveConditionDays(0);
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}