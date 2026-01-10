using Cysharp.Text;

namespace GLOW.Core.Domain.ValueObjects
{
    public record MissionBonusPointIconAssetPath(string Value)
    {
        const string AssetPathFormat = "player_resource_icon_{0}";

        public static MissionBonusPointIconAssetPath FromAssetKey(DiamondAssetKey assetKey)
        {
            return new MissionBonusPointIconAssetPath(ZString.Format(AssetPathFormat, assetKey.Value));
        }

        public static MissionBonusPointIconAssetPath FromAssetKey(PlayerResourceAssetKey assetKey)
        {
            return new MissionBonusPointIconAssetPath(ZString.Format(AssetPathFormat, assetKey.Value));
        }

        public PlayerResourceIconAssetPath ToPlayerResourceIconAssetPath()
        {
            return new PlayerResourceIconAssetPath(Value);
        }
    }
}