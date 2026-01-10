using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.BattleResult.Presentation.ViewModels
{
    public record ContinueDiamondViewModel(
        TotalDiamond Cost,
        PaidDiamond BeforePaidDiamond,
        FreeDiamond BeforeFreeDiamond,
        PaidDiamond AfterPaidDiamond,
        FreeDiamond AfterFreeDiamond,
        bool IsLackOfDiamond,
        DefeatBossEnemyCount DefeatedBossCount,
        BossCount TotalBossCount,
        DefeatEnemyCount RemainingTargetEnemyCount);
}
