using System.Collections.Generic;
using GLOW.Scenes.UnitList.Domain.Misc;

namespace GLOW.Scenes.PartyFormation.Domain.Models
{
    public record PartyFormationUnitListModel(
        IReadOnlyList<PartyFormationUnitListCellModel> Units,
        UnitSortFilterCategoryModel CategoryModel);
}
