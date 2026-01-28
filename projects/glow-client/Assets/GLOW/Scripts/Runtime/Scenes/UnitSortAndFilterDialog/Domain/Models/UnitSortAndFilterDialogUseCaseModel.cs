using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Scenes.UnitList.Domain.Constants;
using GLOW.Scenes.UnitList.Domain.Misc;

namespace GLOW.Scenes.UnitSortAndFilterDialog.Domain.Models
{
    public record UnitSortAndFilterDialogUseCaseModel(
        UnitSortFilterCacheType UnitSortFilterCacheType,
        UnitSortFilterCategoryModel CategoryModel,
        IReadOnlyList<MstSeriesModel> MstSeriesModels,
        IReadOnlyList<MstAbilityDescriptionModel> MstAbilityDescriptionModels)
    {
        public static UnitSortAndFilterDialogUseCaseModel Empty { get; } = new UnitSortAndFilterDialogUseCaseModel(
            UnitSortFilterCacheType.UnitList,
            UnitSortFilterCategoryModel.Default,
            new List<MstSeriesModel>(),
            new List<MstAbilityDescriptionModel>());
    }
}
