using System;

namespace GLOW.Scenes.StaminaRecover.Domain.ValueObject
{
    public record RecoveryStaminaMinutes(int Value)
    {
        public static RecoveryStaminaMinutes Empty { get; } = new RecoveryStaminaMinutes(0);
        
        public RemainStaminaRecoverSecond ToRemainStaminaRecoverSecond()
        {
            return new RemainStaminaRecoverSecond(Value * 60);
        }
        
        public TimeSpan ToTimeSpan()
        {
            return TimeSpan.FromMinutes(Value);
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}