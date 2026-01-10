namespace GLOW.Scenes.AdventBattleRanking.Domain.ValueObjects
{
    public record AdventBattleRankingMyselfFlag(bool Value)
    {
        public static AdventBattleRankingMyselfFlag True { get; } = new(true);
        public static AdventBattleRankingMyselfFlag False { get; } = new(false);

        public static implicit operator bool(AdventBattleRankingMyselfFlag flag) => flag.Value;

        public static bool operator true(AdventBattleRankingMyselfFlag flag) => flag.Value;
        public static bool operator false(AdventBattleRankingMyselfFlag flag) => !flag.Value;
    }
}