using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ArtworkSortAndFilterDialog.Domain.Models;
using GLOW.Scenes.ArtworkSortAndFilterDialog.Presentation.ViewModels;
using GLOW.Scenes.UnitSortAndFilterDialog.Domain.Models;

namespace GLOW.Scenes.ArtworkSortAndFilterDialog.Presentation.Translator
{
    public class ArtworkSortAndFilterDialogViewModelTranslator
    {
        public static ArtworkSortAndFilterDialogViewModel Translate(
            ArtworkSortAndFilterDialogUseCaseModel useCaseModel)
        {
            var seriesFilterTitleModels = useCaseModel.MstSeriesModels
                .Select(model =>
                {
                    var seriesLogoImagePath =
                        new SeriesLogoImagePath(SeriesAssetPath.GetSeriesLogoPath(model.SeriesAssetKey.Value));
                    return new SeriesFilterTitleModel(model.Id, seriesLogoImagePath, model.PrefixWord);

                }).ToList();

            return new ArtworkSortAndFilterDialogViewModel(
                useCaseModel.CacheType,
                useCaseModel.CategoryModel,
                seriesFilterTitleModels);
        }
    }
}
