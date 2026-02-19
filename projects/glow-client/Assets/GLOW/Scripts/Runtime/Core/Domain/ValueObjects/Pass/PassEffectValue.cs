using GLOW.Core.Domain.ValueObjects.IdleIncentive;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Pass
{
    public record PassEffectValue(ObscuredLong Value)
    {
        public static PassEffectValue Empty { get; } = new(0);
        
        public static PassEffectValue operator +(PassEffectValue a, PassEffectValue b)
        {
            return new(a.Value + b.Value);
        }

        public override string ToString()
        {
            return Value.ToString();
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
        
        public bool IsZero()
        {
            return Value == 0;
        }
        
        public IdleIncentiveReceiveCount ToIdleIncentiveReceiveCount()
        {
            return new((int)Value);
        }
        
        public Stamina ToStamina()
        {
            return new((int)Value);
        }
    }
}