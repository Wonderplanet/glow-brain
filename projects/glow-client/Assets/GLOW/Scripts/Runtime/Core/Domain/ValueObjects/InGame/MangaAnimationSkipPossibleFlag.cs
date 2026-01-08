namespace GLOW.Core.Domain.ValueObjects.InGame
{
    public record MangaAnimationSkipPossibleFlag(bool Value)
    {
        public static MangaAnimationSkipPossibleFlag True { get; } = new(true);
        public static MangaAnimationSkipPossibleFlag False { get; } = new(false);

        public static implicit operator bool(MangaAnimationSkipPossibleFlag flag) => flag.Value;
    }
}
