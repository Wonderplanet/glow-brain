using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Scenes.GachaRatio.Presentation.ViewModels
{
    public record GachaRatioLineupViewModel(
        RatioProbabilityAmount RatioProbabilityAmount,
        IReadOnlyList<GachaRatioLineupCellViewModel> GashaRatioLineupCellViewModels
    );
}
