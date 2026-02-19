using System;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Mission
{
    public record BeginnerMissionDayNumber(ObscuredInt Value) :  IComparable<BeginnerMissionDayNumber>
    {
        public static BeginnerMissionDayNumber Empty { get; } = new(0);
        public static bool operator >(BeginnerMissionDayNumber a, BeginnerMissionDaysFromStart b)
        {
            return a.Value > b.Value;
        }
        
        public static bool operator <(BeginnerMissionDayNumber a, BeginnerMissionDaysFromStart b)
        {
            return a.Value < b.Value;
        }
        
        public static bool operator >=(BeginnerMissionDayNumber a, BeginnerMissionDaysFromStart b)
        {
            return a.Value >= b.Value;
        }
        
        public static bool operator <=(BeginnerMissionDayNumber a, BeginnerMissionDaysFromStart b)
        {
            return a.Value <= b.Value;
        }
        
        public static BeginnerMissionDayNumber Min(BeginnerMissionDayNumber a, BeginnerMissionDayNumber b)
        {
            return a.Value < b.Value ? a : b;
        }
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
        public int CompareTo(BeginnerMissionDayNumber other)
        {
            if (ReferenceEquals(this, other)) return 0;
            if (ReferenceEquals(null, other)) return 1;
            return Value.CompareTo(other.Value);
        }
    }
}
