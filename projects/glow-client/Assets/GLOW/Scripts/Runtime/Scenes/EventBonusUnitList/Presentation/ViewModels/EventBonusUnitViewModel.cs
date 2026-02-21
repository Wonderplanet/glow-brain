using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.EventBonusUnitList.Presentation.ViewModels
{
    public record EventBonusUnitViewModel(CharacterIconViewModel Icon, EventBonusPercentage Bonus);
}
