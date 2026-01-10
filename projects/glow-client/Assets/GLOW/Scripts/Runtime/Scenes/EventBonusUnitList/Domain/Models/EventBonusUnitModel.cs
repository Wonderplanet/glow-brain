using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.EventBonusUnitList.Domain.Models
{
    public record EventBonusUnitModel(CharacterIconModel Icon, EventBonusPercentage BonusValue);
}
