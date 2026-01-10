using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.ModelFactories
{
    public static class EnemyIconModelFactory
    {
        public static EnemySmallIconModel CreateSmallIcon(MstEnemyCharacterModel mstEnemy)
        {
            return new EnemySmallIconModel(EnemyCharacterSmallIconAssetPath.FromAssetKey(mstEnemy.AssetKey));
        }

        public static EnemySmallIconModel CreateSmallIcon(MstEnemyStageParameterModel mstEnemyStageParameter)
        {
            return new EnemySmallIconModel(EnemyCharacterSmallIconAssetPath.FromAssetKey(mstEnemyStageParameter.AssetKey));
        }
    }
}
