using System.Collections.Generic;

namespace GLOW.Scenes.EncyclopediaSeries.Presentation.ViewModels
{
    public record EncyclopediaSeriesUnitListViewModel(
        IReadOnlyList<EncyclopediaPlayerUnitListCellViewModel> PlayerUnits,
        IReadOnlyList<EncyclopediaEnemyUnitListCellViewModel> EnemyUnits);
}
