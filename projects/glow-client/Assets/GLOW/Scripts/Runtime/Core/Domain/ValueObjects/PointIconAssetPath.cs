using Cysharp.Text;

namespace GLOW.Core.Domain.ValueObjects
{
    public record PointIconAssetPath(string Value)
    {
        const string AssetPathFormat = "point_icon_{0}";

        public static PointIconAssetPath FromAssetKey(PointAssetKey assetKey)
        {
            return new PointIconAssetPath(ZString.Format(AssetPathFormat, assetKey.Value));
        }

        public static PointIconAssetPath FromAssetKey(PlayerResourceAssetKey assetKey)
        {
            return new PointIconAssetPath(ZString.Format(AssetPathFormat, assetKey.Value));
        }

        public PlayerResourceIconAssetPath ToPlayerResourceIconAssetPath()
        {
            return new PlayerResourceIconAssetPath(Value);
        }
    }
}
