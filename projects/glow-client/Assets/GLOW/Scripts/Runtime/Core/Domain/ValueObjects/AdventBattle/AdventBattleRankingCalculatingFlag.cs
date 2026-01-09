namespace GLOW.Core.Domain.ValueObjects.AdventBattle
{
    public record AdventBattleRankingCalculatingFlag(bool Value)
    {
        public static AdventBattleRankingCalculatingFlag True { get; } = new(true);
        public static AdventBattleRankingCalculatingFlag False { get; } = new(false);

        public static implicit operator bool(AdventBattleRankingCalculatingFlag flag) => flag.Value;
    }
}