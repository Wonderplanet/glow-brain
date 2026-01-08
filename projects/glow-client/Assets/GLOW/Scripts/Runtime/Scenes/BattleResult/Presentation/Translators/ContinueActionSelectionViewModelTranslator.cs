using GLOW.Scenes.BattleResult.Domain.Models;
using GLOW.Scenes.BattleResult.Presentation.ViewModels;
using GLOW.Scenes.PassShop.Presentation.Translator;

namespace GLOW.Scenes.BattleResult.Presentation.Translators
{
    public class ContinueActionSelectionViewModelTranslator
    {
        public static ContinueActionSelectionViewModel ToViewModel(ContinueActionSelectionModel model)
        {
            return new ContinueActionSelectionViewModel(
                model.Cost,
                model.RemainingContinueAdCount,
                HeldAdSkipPassInfoViewModelTranslator.ToHeldAdSkipPassInfoViewModel(
                    model.HeldAdSkipPassInfo),
                model.EnemyCountResult.DefeatedBossCount,
                model.EnemyCountResult.TotalBossCount,
                model.EnemyCountResult.RemainingTargetEnemyCount
            );
        }
    }
}
