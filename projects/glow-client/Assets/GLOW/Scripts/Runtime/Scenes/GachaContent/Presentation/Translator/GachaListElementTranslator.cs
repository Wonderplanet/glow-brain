using System.Collections.Generic;
using GLOW.Scenes.GachaContent.Domain.Model;
using GLOW.Scenes.GachaContent.Presentation.ViewModels;
using GLOW.Scenes.GachaList.Domain.Model;
using GLOW.Scenes.GachaList.Presentation.ViewModels;
using GLOW.Scenes.PassShop.Presentation.Translator;

namespace GLOW.Scenes.GachaContent.Presentation.Translator
{
    public class GachaListElementTranslator
    {

        public static GachaListElementViewModel TranslateElement(GachaListElementUseCaseModel model)
        {
            var footerBanner =
                new GachaFooterBannerViewModel(model.OprGachaId, model.GachaFooterBannerUseCaseModel.GachaBannerAssetPath);
            var contentAssetViewModel =
                new GachaContentAssetViewModel(
                    model.GachaContentAssetUseCaseModel.GachaContentAssetPath);

            var contentViewModel = TranslateGachaContentViewModel(model.GachaContentUseCaseModel);

            return new GachaListElementViewModel(
                footerBanner,
                contentAssetViewModel,
                contentViewModel
            );
        }

        static GachaContentViewModel TranslateGachaContentViewModel(GachaContentUseCaseModel model)
        {
            return new GachaContentViewModel(
                model.OprGachaId,
                model.DrawableFlagByHasDrawLimitedCount,
                model.GachaName,
                model.GachaType,
                model.EndAt,
                model.GachaContentDetailButtonFlag,
                model.GachaRemainingTimeText,
                model.GachaThresholdText,
                model.GachaDescription,
                TranslateGachaContentSingleDrawButtonViewModel(model),
                TranslateToGachaContentMultiDrawButtonViewModel(model),
                TranslateAdDrawButtonViewModel(model),
                model.GachaUnlockConditionType,
                model.GachaLogoAssetPath,
                model.PickupMstUnitIds
            );
        }

        static GachaContentSingleDrawButtonViewModel TranslateGachaContentSingleDrawButtonViewModel(GachaContentUseCaseModel model)
        {
            return new GachaContentSingleDrawButtonViewModel(
                model.SingleDrawCostType,
                model.SingleDrawLimitCount,
                model.IsDisplaySingleDrawButton,
                model.IsEnoughSingleDrawCostItem,
                model.SingleDrawCostIconAssetPath,
                model.SingleDrawCostAmount
            );
        }
        static GachaContentMultiDrawButtonViewModel TranslateToGachaContentMultiDrawButtonViewModel(GachaContentUseCaseModel model)
        {
            return new GachaContentMultiDrawButtonViewModel(
                model.MultiDrawCostType,
                model.MultiDrawLimitCount,
                model.IsDisplayMultiDrawButton,
                model.IsEnoughMultiDrawCostItem,
                model.MultiDrawCostIconAssetPath,
                model.MultiDrawCostAmount,
                model.GachaFixedPrizeDescription
            );
        }

        static GachaContentAdDrawButtonViewModel TranslateAdDrawButtonViewModel(GachaContentUseCaseModel model)
        {
            return new GachaContentAdDrawButtonViewModel(
                model.IsDisplayAdGachaDrawButton,
                model.CanAdGachaDraw,
                model.AdGachaResetRemainingText,
                model.AdGachaDrawableCount,
                HeldAdSkipPassInfoViewModelTranslator.ToHeldAdSkipPassInfoViewModel(model.HeldAdSkipPassInfoModel)
            );
        }
    }
}
