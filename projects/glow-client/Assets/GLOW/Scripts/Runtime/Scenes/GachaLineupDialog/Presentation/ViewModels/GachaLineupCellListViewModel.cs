using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Scenes.GachaLineupDialog.Presentation.ViewModels
{
    public record GachaLineupCellListViewModel(
        RatioProbabilityAmount RatioProbabilityAmount,
        IReadOnlyList<GachaLineupCellViewModel> GachaLineupCellViewModels
    );
}