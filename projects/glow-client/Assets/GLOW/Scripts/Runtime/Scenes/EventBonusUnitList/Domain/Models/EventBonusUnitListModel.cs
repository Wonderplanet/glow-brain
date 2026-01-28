using System.Collections.Generic;

namespace GLOW.Scenes.EventBonusUnitList.Domain.Models
{
    public record EventBonusUnitListModel(IReadOnlyList<EventBonusUnitModel> BonusUnits);
}
