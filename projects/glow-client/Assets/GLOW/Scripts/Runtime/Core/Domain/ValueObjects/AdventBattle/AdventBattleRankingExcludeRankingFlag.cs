namespace GLOW.Core.Domain.ValueObjects.AdventBattle
{
    public record AdventBattleRankingExcludeRankingFlag(bool Value)
    {
        public static AdventBattleRankingExcludeRankingFlag True => new AdventBattleRankingExcludeRankingFlag(true);
        public static AdventBattleRankingExcludeRankingFlag False => new AdventBattleRankingExcludeRankingFlag(false);

        public static implicit operator bool(AdventBattleRankingExcludeRankingFlag flag) => flag.Value;
    }
}