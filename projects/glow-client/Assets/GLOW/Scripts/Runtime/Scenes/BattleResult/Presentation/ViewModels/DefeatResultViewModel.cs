using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.BattleResult.Presentation.ViewModels
{
    public record DefeatResultViewModel(
        StageResultTips Tips,
        DefeatBossEnemyCount DefeatedBossCount,
        BossCount TotalBossCount,
        DefeatEnemyCount RemainingTargetEnemyCount,
        RetryAvailableFlag IsRetryAvailable
    );
}
