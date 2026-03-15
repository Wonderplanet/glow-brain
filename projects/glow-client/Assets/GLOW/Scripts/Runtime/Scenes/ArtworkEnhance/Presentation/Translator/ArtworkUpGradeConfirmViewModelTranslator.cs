using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Presentation.Translators;
using GLOW.Scenes.ArtworkEnhance.Domain.UseCaseModel;
using GLOW.Scenes.ArtworkEnhance.Presentation.ViewModels;

namespace GLOW.Scenes.ArtworkEnhance.Presentation.Translator
{
    public class ArtworkUpGradeConfirmViewModelTranslator
    {
        public static ArtworkUpGradeConfirmViewModel Translate(
            ArtworkUpGradeConfirmUseCaseModel useCaseModel)
        {
            var requiredEnhanceItemViewModels =
                TranslateRequiredEnhanceItemViewModels(useCaseModel.RequiredEnhanceItemModels);

            return new ArtworkUpGradeConfirmViewModel(
                useCaseModel.ArtworkName,
                requiredEnhanceItemViewModels,
                useCaseModel.CurrentGradeLevel,
                useCaseModel.NextGradeLevel,
                useCaseModel.EffectDescription,
                useCaseModel.GradeMaxLimitFlag);
        }

        static IReadOnlyList<RequiredEnhanceItemViewModel> TranslateRequiredEnhanceItemViewModels(
            IReadOnlyList<RequiredEnhanceItemUseCaseModel> useCaseModels)
        {
            return useCaseModels
                .Select(model => new RequiredEnhanceItemViewModel(
                    PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModel(model.IconModel),
                    model.PossessionAmount,
                    model.ConsumeAmount))
                .ToList();
        }
    }
}
