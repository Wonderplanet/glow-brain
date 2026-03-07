using GLOW.Scenes.ArtworkEnhance.Domain.UseCaseModel;
using GLOW.Scenes.ArtworkEnhance.Presentation.ViewModels;

namespace GLOW.Scenes.ArtworkEnhance.Presentation.Translator
{
    public class ArtworkGradeUpAnimViewModelTranslator
    {
        public static ArtworkGradeUpAnimViewModel Translate(
            ArtworkGradeUpAnimUseCaseModel useCaseModel)
        {
            return new ArtworkGradeUpAnimViewModel(
                useCaseModel.ArtworkName,
                useCaseModel.BeforeGradeLevel,
                useCaseModel.AfterGradeLevel,
                useCaseModel.EffectDescription,
                useCaseModel.IsGradeMaxLimit);
        }
    }
}
