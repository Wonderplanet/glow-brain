using GLOW.Scenes.BattleResult.Domain.Models;
using GLOW.Scenes.BattleResult.Presentation.ViewModels;

namespace GLOW.Scenes.BattleResult.Presentation.Translators
{
    public static class DefeatResultViewModelTranslator
    {
        public static DefeatResultViewModel ToDefeatResultViewModel(DefeatResultModel model)
        {
            return new DefeatResultViewModel(
                model.Tips,
                model.EnemyCountResult.DefeatedBossCount,
                model.EnemyCountResult.TotalBossCount,
                model.EnemyCountResult.RemainingTargetEnemyCount,
                model.InGameRetryModel.IsRetryAvailable
            );
        }
    }
}

