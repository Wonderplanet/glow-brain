using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Scenes.ArtworkSortAndFilterDialog.Domain.Constants;
using GLOW.Scenes.UnitList.Domain.Misc;

namespace GLOW.Scenes.ArtworkSortAndFilterDialog.Domain.Models
{
    public record ArtworkSortAndFilterDialogUseCaseModel(
        ArtworkSortFilterCacheType CacheType,
        ArtworkSortFilterCategoryModel CategoryModel,
        IReadOnlyList<MstSeriesModel> MstSeriesModels)
    {
            public static ArtworkSortAndFilterDialogUseCaseModel Empty { get; } = new ArtworkSortAndFilterDialogUseCaseModel(
                ArtworkSortFilterCacheType.ArtworkList,
                ArtworkSortFilterCategoryModel.Default,
                new List<MstSeriesModel>());
    }
}
