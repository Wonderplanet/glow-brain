using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.AdventBattle.Domain.Model;
using GLOW.Scenes.AdventBattle.Presentation.ViewModel.AdventBattleInfo;

namespace GLOW.Scenes.AdventBattle.Presentation.Translator
{
    public class AdventBattleInfoEnemyViewModelTranslator
    {
        public static AdventBattleInfoEnemyViewModel ToAdventBattleInfoEnemyViewModel(AdventBattleInfoEnemyModel model)
        {
            return new AdventBattleInfoEnemyViewModel(
                model.MstEnemyId,
                model.EnemyName,
                model.EnemyColor,
                model.EnemyUnitRoleType,
                model.EnemyUnitKind,
                EnemyCharacterIconAssetPath.FromAssetKey(model.EnemyIconAssetKey),
                model.SortOrder
            );
        }
    }
}