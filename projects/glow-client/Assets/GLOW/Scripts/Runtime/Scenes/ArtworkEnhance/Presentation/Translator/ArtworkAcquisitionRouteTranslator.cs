using System.Linq;
using GLOW.Scenes.ArtworkEnhance.Domain.UseCaseModel;
using GLOW.Scenes.ArtworkEnhance.Presentation.ViewModels;
using GLOW.Scenes.EncyclopediaArtworkDetail.Presentation.ViewModels;

namespace GLOW.Scenes.ArtworkEnhance.Presentation.Translator
{
    public class ArtworkAcquisitionRouteTranslator
    {
        public static ArtworkAcquisitionRouteViewModel Translate(ArtworkAcquisitionRouteUseCaseModel useCaseModel)
        {
            var fragmentListCellViewModels = useCaseModel.FragmentListCellModels
                .Select(model => new EncyclopediaArtworkFragmentListCellViewModel(
                    model.MstArtworkFragmentId,
                    model.QuestType,
                    model.AssetPath,
                    model.Num,
                    model.FragmentName,
                    model.FragmentRarity,
                    model.DropConditionText,
                    model.StatusFlags))
                .ToList();

            var artworkAcquisitionRouteCellViewModels = useCaseModel.AcquisitionRoutes
                .Select(model => new ArtworkAcquisitionRouteCellViewModel(
                    model.ArtworkAcquisitionRouteName,
                    model.Type))
                .ToList();

            return new ArtworkAcquisitionRouteViewModel(
                fragmentListCellViewModels,
                artworkAcquisitionRouteCellViewModels);
        }
    }
}
