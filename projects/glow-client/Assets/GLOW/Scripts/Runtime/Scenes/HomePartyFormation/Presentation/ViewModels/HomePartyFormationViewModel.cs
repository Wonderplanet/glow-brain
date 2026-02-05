using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
namespace GLOW.Scenes.HomePartyFormation.Presentation.Presenters
{
    public record HomePartyFormationViewModel(IReadOnlyDictionary<UserDataId, InGameSpecialRuleAchievedFlag> UnitList);
}

