using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.EncyclopediaSeries.Domain.Models
{
    public record EncyclopediaSeriesUnitListModel(
        IReadOnlyList<EncyclopediaPlayerUnitListCellModel> PlayerUnits,
        IReadOnlyList<EncyclopediaSeriesEnemyListCellModel> EnemyUnits);
}
