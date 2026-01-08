namespace GLOW.Scenes.PvpRanking.Domain.ValueObjects
{
    public record PvpRankingAchieveRankingFlag(bool Value)
    {
        public static PvpRankingAchieveRankingFlag True { get; } = new(true);
        public static PvpRankingAchieveRankingFlag False { get; } = new(false);

        public static implicit operator bool(PvpRankingAchieveRankingFlag flag) => flag.Value;

        public static bool operator true(PvpRankingAchieveRankingFlag flag) => flag.Value;
        public static bool operator false(PvpRankingAchieveRankingFlag flag) => !flag.Value;
    }
}
