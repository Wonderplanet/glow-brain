namespace GLOW.Core.Domain.ValueObjects.InGame
{
    public record MangaAnimationAssetKey(string Value)
    {
        public static MangaAnimationAssetKey Empty { get; } = new(string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
