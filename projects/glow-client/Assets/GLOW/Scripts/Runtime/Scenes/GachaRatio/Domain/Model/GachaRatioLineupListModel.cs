using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Scenes.GachaRatio.Domain.Model
{
    public record GachaRatioLineupListModel(
        GachaRatioLineupModel URareLineupModel,
        GachaRatioLineupModel SSRareLineupModel,
        GachaRatioLineupModel SRareLineupModel,
        GachaRatioLineupModel RLineupModel)
    {
        public static GachaRatioLineupListModel Empty { get; } = new GachaRatioLineupListModel(
            new GachaRatioLineupModel(new RatioProbabilityAmount(Rarity.UR,0), new List<GachaRatioLineupCellModel>()),
            new GachaRatioLineupModel(new RatioProbabilityAmount(Rarity.SSR,0), new List<GachaRatioLineupCellModel>()),
            new GachaRatioLineupModel(new RatioProbabilityAmount(Rarity.SR,0), new List<GachaRatioLineupCellModel>()),
            new GachaRatioLineupModel(new RatioProbabilityAmount(Rarity.R,0), new List<GachaRatioLineupCellModel>())
        );
    }
}
