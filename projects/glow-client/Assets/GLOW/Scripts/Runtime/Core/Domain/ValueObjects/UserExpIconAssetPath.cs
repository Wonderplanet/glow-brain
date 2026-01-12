using Cysharp.Text;

namespace GLOW.Core.Domain.ValueObjects
{
    public record UserExpIconAssetPath(string Value)
    {
        const string AssetPathFormat = "player_resource_icon_{0}";

        public static UserExpIconAssetPath FromAssetKey(UserExpAssetKey assetKey)
        {
            return new UserExpIconAssetPath(ZString.Format(AssetPathFormat, assetKey.Value));
        }

        public static UserExpIconAssetPath FromAssetKey(PlayerResourceAssetKey assetKey)
        {
            return new UserExpIconAssetPath(ZString.Format(AssetPathFormat, assetKey.Value));
        }

        public PlayerResourceIconAssetPath ToPlayerResourceIconAssetPath()
        {
            return new PlayerResourceIconAssetPath(Value);
        }
    }
}
