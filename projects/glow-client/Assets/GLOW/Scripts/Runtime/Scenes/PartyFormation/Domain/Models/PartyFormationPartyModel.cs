using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.PartyFormation.Domain.ValueObjects;

namespace GLOW.Scenes.PartyFormation.Domain.Models
{
    public record PartyFormationPartyModel(
        PartyNo PartyNo,
        PartyName Name,
        TotalPartyStatus TotalPartyStatus,
        TotalPartyStatusUpperArrowFlag TotalPartyStatusUpperArrowFlag,
        PartyMemberSlotCount SlotCount,
        SpecialRulePartyUnitNum SpecialRulePartyUnitNum,
        IReadOnlyList<PartyFormationPartyMemberModel> Members);
}
