namespace GLOW.Core.Domain.ValueObjects.AdventBattle
{
    public record AdventBattleRankingAchieveRankingFlag(bool Value)
    {
        public static AdventBattleRankingAchieveRankingFlag True { get; } = new(true);
        public static AdventBattleRankingAchieveRankingFlag False { get; } = new(false);

        public static implicit operator bool(AdventBattleRankingAchieveRankingFlag flag) => flag.Value;
    }
}
