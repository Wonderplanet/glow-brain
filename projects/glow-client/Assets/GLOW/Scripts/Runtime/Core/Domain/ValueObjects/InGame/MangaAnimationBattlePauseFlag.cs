namespace GLOW.Core.Domain.ValueObjects.InGame
{
    public record MangaAnimationBattlePauseFlag(bool Value)
    {
        public static MangaAnimationBattlePauseFlag True { get; } = new(true);
        public static MangaAnimationBattlePauseFlag False { get; } = new(false);

        public static implicit operator bool(MangaAnimationBattlePauseFlag flag) => flag.Value;
    }
}
