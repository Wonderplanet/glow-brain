using Cysharp.Text;

namespace GLOW.Core.Domain.ValueObjects
{
    public record EmblemIconAssetPath(string Value)
    {
        const string AssetPathFormat = "emblem_icon_{0}";

        public static EmblemIconAssetPath FromAssetKey(EmblemAssetKey assetKey)
        {
            return new EmblemIconAssetPath(ZString.Format(AssetPathFormat, assetKey.Value));
        }

        public static EmblemIconAssetPath FromAssetKey(PlayerResourceAssetKey key)
        {
            return new EmblemIconAssetPath(ZString.Format(AssetPathFormat, key.Value));
        }

        public PlayerResourceIconAssetPath ToPlayerResourceIconAssetPath()
        {
            return new PlayerResourceIconAssetPath(Value);
        }

        public static EmblemIconAssetPath Empty { get; } = new EmblemIconAssetPath(string.Empty);
        public bool IsEmpty() => ReferenceEquals(this, Empty);
    }
}
