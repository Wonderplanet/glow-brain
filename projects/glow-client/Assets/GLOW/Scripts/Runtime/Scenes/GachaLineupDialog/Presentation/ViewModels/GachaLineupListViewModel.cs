using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Scenes.GachaLineupDialog.Presentation.ViewModels
{
    public record GachaLineupListViewModel(
        GachaLineupCellListViewModel URareLineupCellListViewModel,
        GachaLineupCellListViewModel SSRareLineupCellListViewModel,
        GachaLineupCellListViewModel SRareLineupCellListViewModel,
        GachaLineupCellListViewModel RLineupCellListViewModel
    )
    {
        public int TotalCellCount =>
            URareLineupCellListViewModel.GachaLineupCellViewModels.Count +
            SSRareLineupCellListViewModel.GachaLineupCellViewModels.Count +
            SRareLineupCellListViewModel.GachaLineupCellViewModels.Count +
            RLineupCellListViewModel.GachaLineupCellViewModels.Count;
        
        public static GachaLineupListViewModel Empty { get; } = new GachaLineupListViewModel(
            new GachaLineupCellListViewModel(new RatioProbabilityAmount(Rarity.UR, 0), new List<GachaLineupCellViewModel>()),
            new GachaLineupCellListViewModel(new RatioProbabilityAmount(Rarity.SSR, 0), new List<GachaLineupCellViewModel>()),
            new GachaLineupCellListViewModel(new RatioProbabilityAmount(Rarity.SR, 0), new List<GachaLineupCellViewModel>()),
            new GachaLineupCellListViewModel(new RatioProbabilityAmount(Rarity.R, 0), new List<GachaLineupCellViewModel>())
        );
    }
}