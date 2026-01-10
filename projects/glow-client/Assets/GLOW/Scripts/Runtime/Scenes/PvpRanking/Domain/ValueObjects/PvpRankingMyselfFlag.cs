namespace GLOW.Scenes.PvpRanking.Domain.ValueObjects
{
    public record PvpRankingMyselfFlag(bool Value)
    {
        public static PvpRankingMyselfFlag True { get; } = new(true);
        public static PvpRankingMyselfFlag False { get; } = new(false);

        public static implicit operator bool(PvpRankingMyselfFlag flag) => flag.Value;

        public static bool operator true(PvpRankingMyselfFlag flag) => flag.Value;
        public static bool operator false(PvpRankingMyselfFlag flag) => !flag.Value;
    }
}