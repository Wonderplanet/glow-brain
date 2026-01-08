using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.GachaHistoryDialog.Domain.Models
{
    public record GachaHistoryDetailModel(IReadOnlyList<GachaHistoryDetailCellModel> CellModels);
}