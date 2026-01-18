using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Scenes.GachaRatio.Presentation.ViewModels
{
    public record GachaRatioLineupListViewModel(
        GachaRatioLineupViewModel URareLineupViewModel,
        GachaRatioLineupViewModel SSRareLineupViewModel,
        GachaRatioLineupViewModel SRareLineupViewModel,
        GachaRatioLineupViewModel RLineupViewModel
    )
    {
        public int TotalCellCount =>
            URareLineupViewModel.GashaRatioLineupCellViewModels.Count +
            SSRareLineupViewModel.GashaRatioLineupCellViewModels.Count +
            SRareLineupViewModel.GashaRatioLineupCellViewModels.Count +
            RLineupViewModel.GashaRatioLineupCellViewModels.Count;
        
        public static GachaRatioLineupListViewModel Empty { get; } = new GachaRatioLineupListViewModel(
            new GachaRatioLineupViewModel(new RatioProbabilityAmount(Rarity.UR, 0), new List<GachaRatioLineupCellViewModel>()),
            new GachaRatioLineupViewModel(new RatioProbabilityAmount(Rarity.SSR, 0), new List<GachaRatioLineupCellViewModel>()),
            new GachaRatioLineupViewModel(new RatioProbabilityAmount(Rarity.SR, 0), new List<GachaRatioLineupCellViewModel>()),
            new GachaRatioLineupViewModel(new RatioProbabilityAmount(Rarity.R, 0), new List<GachaRatioLineupCellViewModel>())
        );
    }
}
