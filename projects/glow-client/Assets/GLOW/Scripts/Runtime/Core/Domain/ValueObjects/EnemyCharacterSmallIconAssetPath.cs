using Cysharp.Text;

namespace GLOW.Core.Domain.ValueObjects
{
    public record EnemyCharacterSmallIconAssetPath(string Value)
    {
        const string AssetPathFormat = "unit_enemy_icon_{0}_s";

        public static EnemyCharacterSmallIconAssetPath FromAssetKey(UnitAssetKey assetKey)
        {
            return new EnemyCharacterSmallIconAssetPath(ZString.Format(AssetPathFormat, assetKey.Value));
        }
    }
}
