using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Core.Domain.Repositories
{
    public interface IStageEnemyParameterCoef
    {
        EnemyParameterCoef MobEnemyHpCoef { get; }
        EnemyParameterCoef MobEnemyAttackCoef { get; }
        EnemyParameterCoef MobEnemySpeedCoef { get; }
        EnemyParameterCoef BossEnemyHpCoef { get; }
        EnemyParameterCoef BossEnemyAttackCoef { get; }
        EnemyParameterCoef BossEnemySpeedCoef { get; }
    }
}
