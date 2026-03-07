using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.UnitList.Domain.Misc;

namespace GLOW.Scenes.ArtworkFormation.Domain.Models
{
    public record ArtworkFormationUseCaseModel(
        IReadOnlyList<MasterDataId> AssignedFormationArtworkIds,
        IReadOnlyList<ArtworkFormationArtworkModel> ArtworkListModels,
        IReadOnlyList<ArtworkFormationArtworkModel> AllArtworkModels,
        ArtworkSortFilterCategoryModel SortFilterCategoryModel);
}
