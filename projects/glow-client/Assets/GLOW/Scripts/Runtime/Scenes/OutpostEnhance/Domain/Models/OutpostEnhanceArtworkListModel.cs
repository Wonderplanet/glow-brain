using System.Collections.Generic;

namespace GLOW.Scenes.OutpostEnhance.Domain.Models
{
    public record OutpostEnhanceArtworkListModel(IReadOnlyList<OutpostEnhanceArtworkListCellModel> Cells);
}
