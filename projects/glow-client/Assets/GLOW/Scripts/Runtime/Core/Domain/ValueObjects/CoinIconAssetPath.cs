using Cysharp.Text;

namespace GLOW.Core.Domain.ValueObjects
{
    public record CoinIconAssetPath(string Value)
    {
        const string AssetPathFormat = "player_resource_icon_{0}";

        public static CoinIconAssetPath FromAssetKey(CoinAssetKey assetKey)
        {
            return new CoinIconAssetPath(ZString.Format(AssetPathFormat, assetKey.Value));
        }

        public static CoinIconAssetPath FromAssetKey(PlayerResourceAssetKey assetKey)
        {
            return new CoinIconAssetPath(ZString.Format(AssetPathFormat, assetKey.Value));
        }

        public PlayerResourceIconAssetPath ToPlayerResourceIconAssetPath()
        {
            return new PlayerResourceIconAssetPath(Value);
        }
    }
}
