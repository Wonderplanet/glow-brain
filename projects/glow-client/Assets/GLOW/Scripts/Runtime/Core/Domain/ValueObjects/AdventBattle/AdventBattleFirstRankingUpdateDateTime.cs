using System;

namespace GLOW.Core.Domain.ValueObjects.AdventBattle
{
    public record AdventBattleFirstRankingUpdateDateTime(DateTimeOffset Value)
    {
        public static AdventBattleFirstRankingUpdateDateTime Empty { get; } = new(DateTimeOffset.MinValue);

        public bool IsEmpty() => ReferenceEquals(this, Empty);

        public static bool operator < (AdventBattleFirstRankingUpdateDateTime a,DateTimeOffset b) => a.Value < b;
        public static bool operator > (AdventBattleFirstRankingUpdateDateTime a,DateTimeOffset b) => a.Value > b;
        public static bool operator <= (AdventBattleFirstRankingUpdateDateTime a,DateTimeOffset b) => a.Value <= b;
        public static bool operator >= (AdventBattleFirstRankingUpdateDateTime a,DateTimeOffset b) => a.Value >= b;
    };
}
