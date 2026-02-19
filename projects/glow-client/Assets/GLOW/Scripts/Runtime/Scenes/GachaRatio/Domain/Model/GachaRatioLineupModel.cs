using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Scenes.GachaRatio.Domain.Model
{
    public record GachaRatioLineupModel(
        RatioProbabilityAmount RatioProbabilityAmount,
        IReadOnlyList<GachaRatioLineupCellModel> GashaRatioLineupCellModels
    );
}
