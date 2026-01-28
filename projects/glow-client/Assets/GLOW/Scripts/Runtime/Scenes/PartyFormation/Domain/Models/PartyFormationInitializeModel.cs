using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.PartyFormation.Domain.Models
{
    public record PartyFormationInitializeModel(
        PartyNo InitialPartyNo,
        PartyMemberSlotCount ActivePartyMemberSlotCount,
        PartyActiveCount ActivePartyCount,
        PartySpecialUnitAssignLimit PartySpecialUnitAssignLimit,
        ExistsPartySpecialRuleFlag ExistsSpecialRule
        );
}
