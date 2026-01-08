namespace GLOW.Core.Domain.ValueObjects.InGame
{
    public record MangaAnimationAssetPath(string Value)
    {
        const string AssetPathFormat = "manga_animation_{0}";

        public static MangaAnimationAssetPath Empty { get; } = new(string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public static MangaAnimationAssetPath FromAssetKey(MangaAnimationAssetKey assetKey)
        {
            return new MangaAnimationAssetPath(string.Format(AssetPathFormat, assetKey.Value));
        }
    }
}
