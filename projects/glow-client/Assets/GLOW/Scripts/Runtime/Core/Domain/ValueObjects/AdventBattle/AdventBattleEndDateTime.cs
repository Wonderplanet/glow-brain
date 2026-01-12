using System;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.AdventBattle
{
    public record AdventBattleEndDateTime(ObscuredDateTimeOffset Value) : IComparable<AdventBattleEndDateTime>
    {
        public static AdventBattleEndDateTime Empty { get; } = new(DateTimeOffset.MinValue);

        public static bool operator < (AdventBattleEndDateTime a,DateTimeOffset b) => a.Value < b;
        public static bool operator > (AdventBattleEndDateTime a,DateTimeOffset b) => a.Value > b;

        public static bool operator < (DateTimeOffset a,AdventBattleEndDateTime b) => a < b.Value;
        public static bool operator > (DateTimeOffset a,AdventBattleEndDateTime b) => a > b.Value;

        public static bool operator < (AdventBattleEndDateTime a,AdventBattleEndDateTime b) => a.Value < b.Value;
        public static bool operator > (AdventBattleEndDateTime a,AdventBattleEndDateTime b) => a.Value > b.Value;
        
        public static TimeSpan operator -(DateTimeOffset a, AdventBattleEndDateTime b)
        {
            return a - b.Value;
        }
        
        public bool IsEmpty() => ReferenceEquals(this, Empty);

        public int CompareTo(AdventBattleEndDateTime other)
        {
            return Value.CompareTo(other.Value);
        }
    };
}
