using System;
using GLOW.Core.Extensions;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.AdventBattle
{
    public record AdventBattleStartDateTime(ObscuredDateTimeOffset Value)
    {
        public static AdventBattleStartDateTime Empty { get; } = new(DateTimeOffset.MinValue);

        public bool IsEmpty() => ReferenceEquals(this, Empty);

        public static bool operator <= (AdventBattleStartDateTime a,DateTimeOffset b) => a.Value <= b;
        public static bool operator >= (AdventBattleStartDateTime a,DateTimeOffset b) => a.Value >= b;
        
        public AdventBattleStartDateTime AddMinutes(int minutes)
        {
            return new AdventBattleStartDateTime(Value.AddMinutes(minutes));
        }
    };
}
