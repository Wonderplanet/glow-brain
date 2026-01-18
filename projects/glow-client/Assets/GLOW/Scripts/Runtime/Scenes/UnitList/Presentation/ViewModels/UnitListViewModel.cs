using System.Collections.Generic;
using GLOW.Scenes.UnitList.Domain.Constants;
using GLOW.Scenes.UnitList.Domain.Misc;

namespace GLOW.Scenes.UnitList.Presentation.ViewModels
{
    public record UnitListViewModel(
        IReadOnlyList<UnitListCellViewModel> Units,
        UnitSortFilterCategoryModel CategoryModel)
    {
        public UnitListSortOrder SortOrder { get; } = CategoryModel.SortOrder;
    }
}
