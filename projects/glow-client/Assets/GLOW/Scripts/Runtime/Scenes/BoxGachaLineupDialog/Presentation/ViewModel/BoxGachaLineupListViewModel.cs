using System.Collections.Generic;
using GLOW.Core.Domain.Constants;

namespace GLOW.Scenes.BoxGachaLineupDialog.Presentation.ViewModel
{
    public record BoxGachaLineupListViewModel(
        Rarity Rarity,
        IReadOnlyList<BoxGachaLineupCellViewModel> LineupCells)
    {
        public static BoxGachaLineupListViewModel Empty { get; } = new BoxGachaLineupListViewModel(
            Rarity.R,
            new List<BoxGachaLineupCellViewModel>()
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}