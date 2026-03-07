using System.Collections.Generic;
using GLOW.Scenes.ArtworkSortAndFilterDialog.Domain.Constants;
using GLOW.Scenes.UnitList.Domain.Misc;
using GLOW.Scenes.UnitSortAndFilterDialog.Domain.Models;

namespace GLOW.Scenes.ArtworkSortAndFilterDialog.Presentation.ViewModels
{
    public record ArtworkSortAndFilterDialogViewModel(
        ArtworkSortFilterCacheType CacheType,
        ArtworkSortFilterCategoryModel CategoryModel,
        IReadOnlyList<SeriesFilterTitleModel> SeriesFilterTitleModels)
    {
        public static ArtworkSortAndFilterDialogViewModel Empty { get; } = new ArtworkSortAndFilterDialogViewModel(
            ArtworkSortFilterCacheType.ArtworkList,
            ArtworkSortFilterCategoryModel.Default,
            new List<SeriesFilterTitleModel>());
    }
}
