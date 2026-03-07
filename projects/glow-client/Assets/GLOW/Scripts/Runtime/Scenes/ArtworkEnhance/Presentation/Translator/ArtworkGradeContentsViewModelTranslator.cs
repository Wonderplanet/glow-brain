using System.Linq;
using GLOW.Core.Presentation.Translators;
using GLOW.Scenes.ArtworkEnhance.Domain.UseCaseModel;
using GLOW.Scenes.ArtworkEnhance.Presentation.ViewModels;

namespace GLOW.Scenes.ArtworkEnhance.Presentation.Translator
{
    public class ArtworkGradeContentsViewModelTranslator
    {
        public static ArtworkGradeContentsViewModel Translate(ArtworkGradeContentsUseCaseModel useCaseModel)
        {
            var cellViewModels = useCaseModel.CellUseCaseModels
                .Select(model =>
                {
                    return new ArtworkGradeContentCellViewModel(
                        model.ArtworkName,
                        PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModels(model.RequiredItemIconViewModels),
                        model.RequiredGradeLevel,
                        model.TargetGradeLevel,
                        model.IsGradeReleased,
                        model.IsGradeMaxLimit);
                })
                .ToList();

            return new ArtworkGradeContentsViewModel(cellViewModels);
        }
    }
}
