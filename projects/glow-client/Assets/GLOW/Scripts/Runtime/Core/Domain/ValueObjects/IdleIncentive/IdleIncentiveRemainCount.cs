using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.IdleIncentive
{
    public record IdleIncentiveRemainCount(ObscuredInt Value)
    {
        public static IdleIncentiveRemainCount Empty { get; } = new(0);
        
        public bool IsZero()
        {
            return Value == 0;
        }
        
    }
}
