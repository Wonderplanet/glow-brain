using GLOW.Core.Presentation.Translators;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.ComeBackDailyBonus.Domain.Model;

namespace GLOW.Scenes.ComeBackDailyBonus.Presentation.Translator
{
    public static class ComebackDailyBonusCellViewModelTranslator
    {
        public static DailyBonusCollectionCellViewModel ToViewModel(ComebackDailyBonusCellModel model)
        {
            return new DailyBonusCollectionCellViewModel(
                model.ComebackDailyBonusReceiveStatus,
                model.LoginDayCount,
                PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModel(model.PlayerResourceModel),
                model.SortOrder);
        }
    }
}