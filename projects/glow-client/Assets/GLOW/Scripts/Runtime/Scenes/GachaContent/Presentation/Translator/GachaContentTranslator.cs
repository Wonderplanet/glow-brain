using System.Collections.Generic;
using GLOW.Scenes.GachaContent.Domain.Model;
using GLOW.Scenes.GachaContent.Presentation.ViewModels;
using GLOW.Scenes.PassShop.Presentation.Translator;
using GLOW.Scenes.PassShop.Presentation.ViewModel;

namespace GLOW.Scenes.GachaContent.Presentation.Translator
{
    public class GachaContentTranslator
    {
        public static GachaContentViewModel Translate(GachaContentUseCaseModel model)
        {
            return new GachaContentViewModel(
                model.GachaId,
                model.DrawableFlagByHasDrawLimitedCount,
                model.GachaName,
                model.GachaType,
                model.EndAt,
                model.IsDisplayAdGachaDrawButton,
                model.CanAdGachaDraw,
                model.AdGachaResetRemainingText,
                model.AdGachaDrawableCount,
                model.GachaRemainingTimeText,
                model.GachaThresholdText,
                model.GachaDescription,
                model.SingleDrawCostType,
                model.SingleDrawLimitCount,
                model.IsDisplaySingleDrawButton,
                model.IsEnoughSingleDrawCostItem,
                model.SingleDrawCostIconAssetPath,
                model.SingleDrawCostAmount,
                model.MultiDrawCostType,
                model.MultiDrawLimitCount,
                model.IsDisplayMultiDrawButton,
                model.IsEnoughMultiDrawCostItem,
                model.MultiDrawCostIconAssetPath,
                model.MultiDrawCostAmount,
                TranslateToGachaDisplayUnitViewModel(model.GachaPickUpUnitModels),
                HeldAdSkipPassInfoViewModelTranslator.ToHeldAdSkipPassInfoViewModel(model.HeldAdSkipPassInfoModel),
                model.GachaFixedPrizeDescription,
                model.GachaUnlockConditionType,
                model.GachaLogoAssetPath
            );
        }

        static List<GachaDisplayUnitViewModel> TranslateToGachaDisplayUnitViewModel(IReadOnlyList<GachaDisplayUnitModel> models)
        {
            var viewModels = new List<GachaDisplayUnitViewModel>();

            foreach (var model in models)
            {
                viewModels.Add(
                    new GachaDisplayUnitViewModel(
                        model.UnitId,
                        model.Name,
                        model.RoleType,
                        model.CharacterColor,
                        model.Rarity,
                        model.SeriesLogoImagePath,
                        model.CutInAssetPath,
                        model.DisplayUnitDescription
                    ));
            }

            return viewModels;
        }
    }
}
