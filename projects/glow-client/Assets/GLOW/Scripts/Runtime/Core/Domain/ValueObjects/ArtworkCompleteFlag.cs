namespace GLOW.Core.Domain.ValueObjects
{
    public record ArtworkCompleteFlag(bool Value)
    {
        public static ArtworkCompleteFlag True { get; } = new ArtworkCompleteFlag(true);
        public static ArtworkCompleteFlag False { get; } = new ArtworkCompleteFlag(false);

        public static implicit operator bool(ArtworkCompleteFlag flag) => flag.Value;
    }
}
