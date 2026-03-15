using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ArtworkFormation.Domain.ValueObjects;
using GLOW.Scenes.UnitList.Domain.Misc;

namespace GLOW.Scenes.ArtworkFormation.Presentation.ViewModels
{
    public record ArtworkFormationListViewModel(
        List<ArtworkFormationListCellViewModel> CellViewModels,
        ArtworkSortFilterCategoryModel SortFilterCategoryModel)
    {
        public ArtworkFormationListViewModel WithUpdatedAssignment(
            MasterDataId mstArtworkId,
            IReadOnlyList<MasterDataId> assignedArtworkIds)
        {
            var contains = assignedArtworkIds.Contains(mstArtworkId);
            var isAssigned = contains ? AssignedFlag.Assigned : AssignedFlag.Unassigned;
            var isPartyFull = assignedArtworkIds.Count >= 10;

            var updatedCells = CellViewModels
                .Select(
                    cell => cell.MstArtworkId == mstArtworkId
                        ? cell with
                        {
                            IsAssigned = isAssigned,
                            IsGrayOut = !isAssigned && isPartyFull
                                ? ArtworkGrayOutFlag.True
                                : ArtworkGrayOutFlag.False
                        }
                        : cell)
                .ToList();

            return this with { CellViewModels = updatedCells };
        }

        public ArtworkFormationListViewModel WithUpdatedAllAssignments(IReadOnlyList<MasterDataId> assignedArtworkIds)
        {
            var isPartyFull = assignedArtworkIds.Count >= 10;

            var updatedCells = CellViewModels
                .Select(cell =>
                {
                    var contains = assignedArtworkIds.Contains(cell.MstArtworkId);
                    var newFlag = contains ? AssignedFlag.Assigned : AssignedFlag.Unassigned;
                    var isGrayOut = !contains && isPartyFull
                        ? ArtworkGrayOutFlag.True
                        : ArtworkGrayOutFlag.False;

                    return cell with
                    {
                        IsAssigned = newFlag,
                        IsGrayOut = isGrayOut
                    };
                })
                .ToList();

            return this with { CellViewModels = updatedCells };
        }
    }
}
