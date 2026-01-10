using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.IdleIncentive
{
    public record IdleIncentiveReceiveCount(ObscuredInt Value)
    {
        public static IdleIncentiveReceiveCount Empty { get; } = new IdleIncentiveReceiveCount(0);

        public bool IsZero()
        {
            return Value == 0;
        }
        
        public static IdleIncentiveReceiveCount operator +(IdleIncentiveReceiveCount a, IdleIncentiveReceiveCount b)
        {
            return new IdleIncentiveReceiveCount(a.Value + b.Value);
        }
        
        public static IdleIncentiveReceiveCount operator -(IdleIncentiveReceiveCount a, IdleIncentiveReceiveCount b)
        {
            return new IdleIncentiveReceiveCount(a.Value - b.Value);
        }
        
        public IdleIncentiveRemainCount ToIdleIncentiveRemainCount()
        {
            return new(Value);
        }
    };
}
