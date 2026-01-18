using System;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Mission
{
    public record BeginnerMissionDaysFromStart(ObscuredInt Value)
    {
        public ObscuredInt Value { get; } = Math.Min(Value, 7);
        
        public static BeginnerMissionDaysFromStart Empty { get; } = new(0);
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public bool IsUnlockDay(BeginnerMissionDayNumber number)
        {
            return Value >= number.Value;
        }
    }
}
