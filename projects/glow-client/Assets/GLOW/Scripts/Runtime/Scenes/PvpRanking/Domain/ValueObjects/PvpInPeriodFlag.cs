namespace GLOW.Scenes.PvpRanking.Domain.ValueObjects
{
    public record PvpInPeriodFlag(bool Value)
    {
        public static PvpInPeriodFlag True { get; } = new(true);
        public static PvpInPeriodFlag False { get; } = new(false);

        public static implicit operator bool(PvpInPeriodFlag flag) => flag.Value;
    }
}