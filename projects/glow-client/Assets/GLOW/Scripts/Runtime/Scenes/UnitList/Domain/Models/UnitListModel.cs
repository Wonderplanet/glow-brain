using System.Collections.Generic;
using GLOW.Scenes.UnitList.Domain.Misc;

namespace GLOW.Scenes.UnitList.Domain.Models
{
    public record UnitListModel(
        IReadOnlyList<UnitListCellModel> Units,
        UnitSortFilterCategoryModel CategoryModel);
}
