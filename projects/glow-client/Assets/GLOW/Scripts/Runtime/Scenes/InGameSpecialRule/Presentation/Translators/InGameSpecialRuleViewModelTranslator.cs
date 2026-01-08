using GLOW.Scenes.InGameSpecialRule.Domain.Models;
using GLOW.Scenes.InGameSpecialRule.Presentation.ViewModels;
using GLOW.Scenes.InGameSpecialRule.Presentation.ValueObjects;

namespace GLOW.Scenes.InGameSpecialRule.Presentation.Translators
{
    public class InGameSpecialRuleViewModelTranslator
    {
        public static InGameSpecialRuleViewModel TranslateInGameSpecialRuleViewModel(
            InGameSpecialRuleModel model,
            InGameSpecialRuleFromUnitSelectFlag isFromUnitSelect)
        {
            if (model.IsEmpty())
            {
                return InGameSpecialRuleViewModel.Empty;
            }

            return new InGameSpecialRuleViewModel(
                model.SeriesLogoImagePathList,
                model.UnitRarities,
                model.UnitRoleTypes,
                model.UnitAmount,
                model.UnitAttackRangeTypes,
                model.EnemyDestructionCount,
                model.SpecificEnemyDestructionTargetName,
                model.SpecificEnemyDestructionCount,
                model.StartOutpostHp,
                model.TimeLimit,
                model.IsDefenseTarget,
                model.IsEnemyDestruction,
                model.IsSpecificEnemyDestruction,
                model.IsStartOutpostHp,
                model.IsEnemyOutpostDamageInvalidation,
                model.IsNoContinue,
                model.IsSpeedAttack,
                isFromUnitSelect,
                model.ExistsFormationRule,
                model.ExistsOtherRule);
        }
    }
}
