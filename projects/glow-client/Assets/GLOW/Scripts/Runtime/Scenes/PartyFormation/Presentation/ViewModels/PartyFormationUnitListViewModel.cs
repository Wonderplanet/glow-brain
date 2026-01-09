using System.Collections.Generic;
using GLOW.Scenes.UnitList.Domain.Constants;
using GLOW.Scenes.UnitList.Domain.Misc;

namespace GLOW.Scenes.PartyFormation.Presentation.ViewModels
{
    public record PartyFormationUnitListViewModel(
        IReadOnlyList<PartyFormationUnitListCellViewModel> Units,
        UnitSortFilterCategoryModel CategoryModel)
    {
        public static PartyFormationUnitListViewModel Empty { get; } = new PartyFormationUnitListViewModel(
            new List<PartyFormationUnitListCellViewModel>(),
            UnitSortFilterCategoryModel.Default
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
