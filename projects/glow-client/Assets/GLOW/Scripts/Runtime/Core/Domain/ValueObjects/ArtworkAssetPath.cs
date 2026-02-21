using Cysharp.Text;

namespace GLOW.Core.Domain.ValueObjects
{
    public record ArtworkAssetPath(string Value)
    {
        public static ArtworkAssetPath Empty = new ArtworkAssetPath(string.Empty);
        public static ArtworkAssetPath Default = new ArtworkAssetPath("artwork_tutorial_0001b");

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public static ArtworkAssetPath Create(ArtworkAssetKey key)
        {
            return new ArtworkAssetPath(ZString.Format("artwork_{0}a", key.Value));
        }

        public static ArtworkAssetPath CreateSmall(ArtworkAssetKey key)
        {
            return key.IsEmpty() ? ArtworkAssetPath.Empty : new ArtworkAssetPath(ZString.Format("artwork_{0}b", key.Value));
        }

        public PlayerResourceIconAssetPath ToPlayerResourceIconAssetPath()
        {
            return new PlayerResourceIconAssetPath(Value);
        }
    }
}
