using System.Collections.Generic;
using GLOW.Scenes.UnitList.Domain.Misc;

namespace GLOW.Scenes.ArtworkList.Domain.Models
{
    public record ArtworkListUseCaseModel(
        IReadOnlyList<ArtworkListCellUseCaseModel> ArtworkList,
        ArtworkSortFilterCategoryModel SortFilterCategoryModel);
}

