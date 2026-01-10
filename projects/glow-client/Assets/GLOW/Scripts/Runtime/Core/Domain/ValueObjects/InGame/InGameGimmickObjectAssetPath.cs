using Cysharp.Text;

namespace GLOW.Core.Domain.ValueObjects.InGame
{
    public record InGameGimmickObjectAssetPath(string Value)
    {
        const string AssetPathFormat = "ingame_gimmick_object_{0}";

        public static InGameGimmickObjectAssetPath FromAssetKey(InGameGimmickObjectAssetKey assetKey)
        {
            return new InGameGimmickObjectAssetPath(ZString.Format(AssetPathFormat, assetKey.Value));
        }
    }
}
