using Cysharp.Text;

namespace GLOW.Core.Domain.ValueObjects.InGame
{
    public record DefenseTargetAssetPath(string Value)
    {
        const string AssetPathFormat = "defense_target_{0}";

        public static DefenseTargetAssetPath FromAssetKey(DefenseTargetAssetKey assetKey)
        {
            return new DefenseTargetAssetPath(ZString.Format(AssetPathFormat, assetKey.Value));
        }
    }
}
