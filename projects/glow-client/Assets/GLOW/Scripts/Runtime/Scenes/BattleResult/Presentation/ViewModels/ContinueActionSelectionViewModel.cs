using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using GLOW.Scenes.PassShop.Presentation.ViewModel;

namespace GLOW.Scenes.BattleResult.Presentation.ViewModels
{
    public record ContinueActionSelectionViewModel(
        TotalDiamond Cost,
        ContinueCount RemainingContinueAdCount,
        HeldAdSkipPassInfoViewModel HeldAdSkipPassInfoViewModel,
        DefeatBossEnemyCount DefeatedBossCount,
        BossCount TotalBossCount,
        DefeatEnemyCount RemainingTargetEnemyCount);
}
