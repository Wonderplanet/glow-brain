using GLOW.Scenes.BattleResult.Domain.Models;
using GLOW.Scenes.BattleResult.Presentation.ViewModels;

namespace GLOW.Scenes.BattleResult.Presentation.Translators
{
    public static class ContinueDiamondViewModelTranslator
    {
        public static ContinueDiamondViewModel ToContinueViewModel(ContinueDiamondModel model)
        {
            return new ContinueDiamondViewModel(
                model.Cost,
                model.BeforePaidDiamond,
                model.BeforeFreeDiamond,
                model.AfterPaidDiamond,
                model.AfterFreeDiamond,
                model.IsLackOfDiamond,
                model.EnemyCountResult.DefeatedBossCount,
                model.EnemyCountResult.TotalBossCount,
                model.EnemyCountResult.RemainingTargetEnemyCount
            );
        }
    }
}

