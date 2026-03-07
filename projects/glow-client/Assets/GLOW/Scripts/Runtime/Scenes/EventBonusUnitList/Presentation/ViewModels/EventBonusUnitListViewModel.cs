using System.Collections.Generic;

namespace GLOW.Scenes.EventBonusUnitList.Presentation.ViewModels
{
    public record EventBonusUnitListViewModel(IReadOnlyList<EventBonusUnitViewModel> BonusUnits);
}
