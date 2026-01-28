using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.PartyFormation.Domain.ValueObjects;

namespace GLOW.Scenes.PartyFormation.Presentation.ViewModels
{
    public record PartyFormationPartyViewModel(
        PartyNo PartyNo,
        PartyName Name,
        TotalPartyStatus TotalPartyStatus,
        TotalPartyStatusUpperArrowFlag TotalPartyStatusUpperArrowFlag,
        PartyMemberSlotCount SlotCount,
        SpecialRulePartyUnitNum SpecialRulePartyUnitNum,
        IReadOnlyList<PartyFormationPartyMemberViewModel> Members);
}
