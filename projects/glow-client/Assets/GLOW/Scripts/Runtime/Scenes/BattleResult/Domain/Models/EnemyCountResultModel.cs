using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.BattleResult.Domain.Models
{
    public record EnemyCountResultModel(
        DefeatBossEnemyCount DefeatedBossCount,
        BossCount TotalBossCount,
        DefeatEnemyCount RemainingTargetEnemyCount
    );
}
