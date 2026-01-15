namespace GLOW.Scenes.PvpRanking.Domain.ValueObjects
{
    public record PvpRankingCalculatingFlag(bool Value)
    {
        public static PvpRankingCalculatingFlag True { get; } = new(true);
        public static PvpRankingCalculatingFlag False { get; } = new(false);

        public static implicit operator bool(PvpRankingCalculatingFlag flag) => flag.Value;

        public static bool operator true(PvpRankingCalculatingFlag flag) => flag.Value;
        public static bool operator false(PvpRankingCalculatingFlag flag) => !flag.Value;
    }
}