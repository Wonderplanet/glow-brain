using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.UnitList.Domain.Constants;
using GLOW.Scenes.UnitList.Domain.Misc;
using GLOW.Scenes.UnitSortAndFilterDialog.Domain.Models;

namespace GLOW.Scenes.UnitSortAndFilterDialog.Presentation.Views
{
    public record UnitSortAndFilterDialogViewModel(
        UnitSortFilterCacheType UnitSortFilterCacheType,
        UnitSortFilterCategoryModel CategoryModel,
        IReadOnlyList<SeriesFilterTitleModel> SeriesFilterTitleModels,
        IReadOnlyList<UnitAbilityFilterTitleModel> UnitAbilityFilterTitleModels,
        MasterDataId SpecialRuleTargetMstStageId,
        InGameContentType SpecialRuleContentType)
    {
        public static UnitSortAndFilterDialogViewModel Empty { get; } = new UnitSortAndFilterDialogViewModel(
            UnitSortFilterCacheType.UnitList,
            UnitSortFilterCategoryModel.Default,
            new List<SeriesFilterTitleModel>(),
            new List<UnitAbilityFilterTitleModel>(),
            MasterDataId.Empty,
            InGameContentType.Stage);
    }
}
