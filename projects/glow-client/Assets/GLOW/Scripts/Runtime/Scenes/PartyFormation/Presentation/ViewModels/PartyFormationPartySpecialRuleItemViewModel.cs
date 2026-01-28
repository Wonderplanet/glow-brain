using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.PartyFormation.Presentation.ViewModels
{
    public record PartyFormationPartySpecialRuleItemViewModel(
        PartyNo PartyNo,
        PartyName Name,
        PartyMemberSlotCount SlotCount,
        IReadOnlyList<PartyFormationPartyMemberViewModel> Members);
}
