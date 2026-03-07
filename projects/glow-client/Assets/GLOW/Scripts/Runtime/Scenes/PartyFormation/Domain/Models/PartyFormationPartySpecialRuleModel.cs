using System.Collections.Generic;

namespace GLOW.Scenes.PartyFormation.Domain.Models
{
    public record PartyFormationPartySpecialRuleModel(IReadOnlyList<PartyFormationPartySpecialRuleItemModel> ItemModels);
}
