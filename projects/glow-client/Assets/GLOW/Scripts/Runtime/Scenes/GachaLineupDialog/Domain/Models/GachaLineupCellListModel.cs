using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Scenes.GachaLineupDialog.Domain.Models
{
    public record GachaLineupCellListModel(
        RatioProbabilityAmount RatioProbabilityAmount,
        IReadOnlyList<GachaLineupCellModel> GachaLineupCellModels)
    {
        public static GachaLineupCellListModel Empty { get; } = new(
            new RatioProbabilityAmount(Rarity.R, 0),
            new List<GachaLineupCellModel>());
    }
}