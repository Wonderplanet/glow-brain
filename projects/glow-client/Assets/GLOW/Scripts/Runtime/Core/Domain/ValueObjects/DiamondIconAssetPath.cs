using Cysharp.Text;

namespace GLOW.Core.Domain.ValueObjects
{
    public record DiamondIconAssetPath(string Value)
    {
        const string AssetPathFormat = "player_resource_icon_{0}";

        public static DiamondIconAssetPath FromAssetKey(DiamondAssetKey assetKey)
        {
            return new DiamondIconAssetPath(ZString.Format(AssetPathFormat, assetKey.Value));
        }

        public static DiamondIconAssetPath FromAssetKey(PlayerResourceAssetKey assetKey)
        {
            return new DiamondIconAssetPath(ZString.Format(AssetPathFormat, assetKey.Value));
        }

        public PlayerResourceIconAssetPath ToPlayerResourceIconAssetPath()
        {
            return new PlayerResourceIconAssetPath(Value);
        }
    }
}
