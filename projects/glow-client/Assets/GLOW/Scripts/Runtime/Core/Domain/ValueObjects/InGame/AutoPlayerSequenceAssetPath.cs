using Cysharp.Text;

namespace GLOW.Core.Domain.ValueObjects.InGame
{
    public record AutoPlayerSequenceAssetPath(string Value)
    {
        const string AssetPathFormat = "ingame_auto_player_sequence_{0}";

        public static AutoPlayerSequenceAssetPath FromInGameAssetKey(InGameAssetKey assetKey)
        {
            return new AutoPlayerSequenceAssetPath(ZString.Format(AssetPathFormat, assetKey.Value));
        }

        public static AutoPlayerSequenceAssetPath FromStageAssetKey(StageAssetKey assetKey)
        {
            return new AutoPlayerSequenceAssetPath(ZString.Format(AssetPathFormat, assetKey.Value));
        }

        public static AutoPlayerSequenceAssetPath FromAssetKey(string assetKey)
        {
            return new AutoPlayerSequenceAssetPath(ZString.Format(AssetPathFormat, assetKey));
        }
    }
}
