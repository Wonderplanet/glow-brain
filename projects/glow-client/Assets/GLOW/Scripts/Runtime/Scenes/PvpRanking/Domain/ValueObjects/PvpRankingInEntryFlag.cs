namespace GLOW.Scenes.PvpRanking.Domain.ValueObjects
{
    public record PvpRankingInEntryFlag(bool Value)
    {
        public static PvpRankingInEntryFlag True { get; } = new(true);
        public static PvpRankingInEntryFlag False { get; } = new(false);

        public static implicit operator bool(PvpRankingInEntryFlag flag) => flag.Value;

        public static bool operator true(PvpRankingInEntryFlag flag) => flag.Value;
        public static bool operator false(PvpRankingInEntryFlag flag) => !flag.Value;
    }
}