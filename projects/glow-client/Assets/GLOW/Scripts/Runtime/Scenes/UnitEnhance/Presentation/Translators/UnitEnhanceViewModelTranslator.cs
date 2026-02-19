using System.Linq;
using Cysharp.Threading.Tasks;
using GLOW.Core.Presentation.Translators;
using GLOW.Scenes.UnitEnhance.Domain.Models;
using GLOW.Scenes.UnitEnhance.Presentation.ViewModels;

namespace GLOW.Scenes.UnitEnhance.Presentation.Translators
{
    public static class UnitEnhanceViewModelTranslator
    {
        public static UnitEnhanceLevelUpTabViewModel TranslateToLevelUpTabViewModel(
            UnitEnhanceLevelUpTabModel levelUpTabModel)
        {
            return new UnitEnhanceLevelUpTabViewModel(
                levelUpTabModel.RoleType,
                TranslateToLevelUpViewModel(levelUpTabModel.LevelUp),
                TranslateToRankUpViewModel(levelUpTabModel.RankUp),
                levelUpTabModel.Hp,
                levelUpTabModel.AttackPower,
                levelUpTabModel.UnitGrade,
                levelUpTabModel.IsGradeUp);
        }

        static UnitEnhanceLevelUpViewModel TranslateToLevelUpViewModel(
            UnitEnhanceLevelUpModel levelUpModel)
        {
            if(levelUpModel.IsEmpty()) return UnitEnhanceLevelUpViewModel.Empty;

            return new UnitEnhanceLevelUpViewModel(
                levelUpModel.LevelUpCost,
                levelUpModel.IsEnoughCost);
        }

        static UnitEnhanceRankUpViewModel TranslateToRankUpViewModel(
            UnitEnhanceRankUpModel rankUpModel)
        {
            if(rankUpModel.IsEmpty()) return UnitEnhanceRankUpViewModel.Empty;

            var items = rankUpModel.CostItems
                .Select(TranslateToRequireItemViewModel)
                .ToList();

            return new UnitEnhanceRankUpViewModel(items);
        }

        public static UnitEnhanceRequireItemViewModel TranslateToRequireItemViewModel(
            UnitEnhanceRequireItemModel model)
        {
            return new UnitEnhanceRequireItemViewModel(
                ItemViewModelTranslator.ToItemIconViewModel(model.Item),
                model.IsEnoughCost
            );
        }
    }
}
