using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.UnitList.Domain.Constants;

namespace GLOW.Scenes.PartyFormation.Presentation.ViewModels
{
    public record PartyFormationInitializeViewModel(
        PartyNo InitialPartyNo,
        PartyMemberSlotCount ActivePartyMemberSlotCount,
        PartyActiveCount ActivePartyCount,
        ExistsPartySpecialRuleFlag ExistsSpecialRule,
        UnitSortFilterCacheType UnitSortFilterCacheType
    );
}
