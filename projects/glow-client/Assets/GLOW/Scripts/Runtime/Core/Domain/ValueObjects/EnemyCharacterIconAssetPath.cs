using Cysharp.Text;

namespace GLOW.Core.Domain.ValueObjects
{
    public record EnemyCharacterIconAssetPath(string Value)
    {
        public static EnemyCharacterIconAssetPath FromAssetKey(UnitAssetKey assetKey)
        {
            return new EnemyCharacterIconAssetPath(ZString.Format("unit_enemy_icon_{0}", assetKey.Value));
        }
    }
}
