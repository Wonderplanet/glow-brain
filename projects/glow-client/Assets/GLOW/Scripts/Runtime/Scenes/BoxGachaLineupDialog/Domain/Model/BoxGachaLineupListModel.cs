using System.Collections.Generic;
using GLOW.Core.Domain.Constants;

namespace GLOW.Scenes.BoxGachaLineupDialog.Domain.Model
{
    public record BoxGachaLineupListModel(
        Rarity Rarity,
        IReadOnlyList<BoxGachaLineupCellModel> LineupCellModels)
    {
        public static BoxGachaLineupListModel Empty { get; } = new BoxGachaLineupListModel(
            Rarity.R,
            new List<BoxGachaLineupCellModel>()
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}