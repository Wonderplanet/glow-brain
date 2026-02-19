namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record ActivatedMangaAnimationFlag(bool Value)
    {
        public static ActivatedMangaAnimationFlag True { get; } = new(true);
        public static ActivatedMangaAnimationFlag False { get; } = new(false);

        public static implicit operator bool(ActivatedMangaAnimationFlag flag) => flag.Value;
    }
}
