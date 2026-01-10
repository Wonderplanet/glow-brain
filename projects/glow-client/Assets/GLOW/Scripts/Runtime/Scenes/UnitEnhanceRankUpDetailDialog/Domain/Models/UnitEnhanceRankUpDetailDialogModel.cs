using System.Collections.Generic;

namespace GLOW.Scenes.UnitEnhanceRankUpDetailDialog.Domain.Models
{
    public record UnitEnhanceRankUpDetailDialogModel(
        IReadOnlyList<UnitEnhanceRankUpDetailCellModel> CellModelList);
}
