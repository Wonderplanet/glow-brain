using System;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.StaminaRecover.Domain.ValueObject
{
    public record RemainStaminaRecoverSecond(int Value)
    {
        public static RemainStaminaRecoverSecond Empty { get; } = new RemainStaminaRecoverSecond(0);
        
        public bool IsZero()
        {
            return Value == 0;
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public TimeSpan ToTimeSpan()
        {
            return TimeSpan.FromSeconds(Value);
        }
    }
}