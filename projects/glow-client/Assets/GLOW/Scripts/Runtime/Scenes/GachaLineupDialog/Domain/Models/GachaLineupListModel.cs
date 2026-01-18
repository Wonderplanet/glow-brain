using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Scenes.GachaLineupDialog.Domain.Models
{
    public record GachaLineupListModel(
        GachaLineupCellListModel URareLineupModel,
        GachaLineupCellListModel SSRareLineupModel,
        GachaLineupCellListModel SRareLineupModel,
        GachaLineupCellListModel RLineupModel)
    {
        public static GachaLineupListModel Empty { get; } = new GachaLineupListModel(
            new GachaLineupCellListModel(new RatioProbabilityAmount(Rarity.UR,0), new List<GachaLineupCellModel>()),
            new GachaLineupCellListModel(new RatioProbabilityAmount(Rarity.SSR,0), new List<GachaLineupCellModel>()),
            new GachaLineupCellListModel(new RatioProbabilityAmount(Rarity.SR,0), new List<GachaLineupCellModel>()),
            new GachaLineupCellListModel(new RatioProbabilityAmount(Rarity.R,0), new List<GachaLineupCellModel>())
        );
    }
}