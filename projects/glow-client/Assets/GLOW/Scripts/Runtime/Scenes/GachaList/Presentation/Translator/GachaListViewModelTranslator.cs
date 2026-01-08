using System.Collections.Generic;
using GLOW.Core.Presentation.Translators;
using GLOW.Scenes.GachaList.Domain.Model;
using GLOW.Scenes.GachaList.Presentation.ViewModels;

namespace GLOW.Scenes.GachaList.Presentation.Translator
{
    public class GachaListViewModelTranslator
    {
        public static GachaBannerViewModel TranslateToGachaBannerViewModel(GachaBannerModel model)
        {
            return new GachaBannerViewModel(
                model.GachaId,
                model.GachaType,
                model.GachaBannerAssetPath,
                model.NotificationBadge,
                model.GachaRemainingTimeText,
                model.GachaDescription,
                model.GachaThresholdText
                );

        }
        public static List<GachaBannerViewModel> TranslateToGachaBannerViewModels(List<GachaBannerModel> models)
        {
            var viewModels = new List<GachaBannerViewModel>();

            foreach (var model in models)
            {
                viewModels.Add(new GachaBannerViewModel(
                    model.GachaId,
                    model.GachaType,
                    model.GachaBannerAssetPath,
                    model.NotificationBadge,
                    model.GachaRemainingTimeText,
                    model.GachaDescription,
                    model.GachaThresholdText
                    )
                );
            }

            return viewModels;
        }
        
        public static List<FestivalGachaBannerViewModel> TranslateToFestivalGachaBannerViewModels(List<FestivalGachaBannerModel> models)
        {
            var viewModels = new List<FestivalGachaBannerViewModel>();

            foreach (var model in models)
            {
                viewModels.Add(new FestivalGachaBannerViewModel(
                    model.GachaId,
                    model.GachaType,
                    model.FestivalGachaBannerAssetPath,
                    model.NotificationBadge,
                    model.GachaRemainingTimeText,
                    model.GachaDescription,
                    model.GachaThresholdText
                    )
                );
            }

            return viewModels;
        }

        public static List<MedalGachaBannerViewModel> TranslateToMedalGachaBannerViewModel(List<MedalGachaModel> models)
        {
            var viewModels = new List<MedalGachaBannerViewModel>();

            foreach (var model in models)
            {
                viewModels.Add(new MedalGachaBannerViewModel(
                        model.GachaId,
                        model.GachaBannerAssetPath,
                        PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModel(model.PlayerResourceModel).AssetPath,
                        model.GachaDescription,
                        model.DrawCostAmount,
                        model.DrawableFlag,
                        model.GachaRemainingTimeText,
                        model.GachaThresholdText
                    )
                );
            }

            return viewModels;
        }

        public static PremiumGachaViewModel TranslateToPremiumGachaViewModel(PremiumGachaModel model)
        {
            if(model.IsEmpty()) return PremiumGachaViewModel.Empty;

            return new PremiumGachaViewModel(
                model.GachaId,
                model.GachaBannerAssetPath,
                model.GachaDescription,
                model.NotificationBadge,
                model.SingleDrawCostType,
                PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModel(model.SinglePlayerResourceModel).AssetPath,
                model.SingleDrawCostAmount,
                PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModel(model.MultiPlayerResourceModel).AssetPath,
                model.MultiDrawCostAmount,
                model.CanAdGachaDraw,
                model.AdGachaResetRemainingText,
                model.AdGachaDrawableCount,
                model.GachaRemainingTimeText,
                model.GachaThresholdText,
                model.GachaFixedPrizeDescription
                );
        }

        public static TutorialGachaBannerViewModel TranslateToTutorialGachaBannerViewModel(GachaBannerModel model)
        {
            if (model.IsEmpty()) return TutorialGachaBannerViewModel.Empty;

            return new TutorialGachaBannerViewModel(
                model.GachaId,
                model.GachaBannerAssetPath,
                model.GachaDescription
            );
        }
        
        static FestivalGachaBannerViewModel TranslateToFestivalGachaBannerViewModel(FestivalGachaBannerModel model)
        {
            return new FestivalGachaBannerViewModel(
                model.GachaId,
                model.GachaType,
                model.FestivalGachaBannerAssetPath,
                model.NotificationBadge,
                model.GachaRemainingTimeText,
                model.GachaDescription,
                model.GachaThresholdText
                );
        }
    }
}
