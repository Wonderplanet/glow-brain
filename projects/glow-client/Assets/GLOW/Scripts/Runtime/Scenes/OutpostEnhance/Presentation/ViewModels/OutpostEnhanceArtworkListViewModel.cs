using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.OutpostEnhance.Presentation.ViewModels
{
    public record OutpostEnhanceArtworkListViewModel(IReadOnlyList<OutpostEnhanceArtworkListCellViewModel> Cells)
    {
        public OutpostEnhanceArtworkListViewModel WithUpdatedSelection(MasterDataId selectedMstArtworkId)
        {
            var updatedCells = Cells
                .Select(cell => cell with { IsSelect = cell.MstArtworkId == selectedMstArtworkId })
                .ToList();

            return this with { Cells = updatedCells };
        }
    }
}
