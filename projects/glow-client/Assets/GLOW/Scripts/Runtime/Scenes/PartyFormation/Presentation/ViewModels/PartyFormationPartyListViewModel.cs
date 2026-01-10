using System.Collections.Generic;

namespace GLOW.Scenes.PartyFormation.Presentation.ViewModels
{
    public record PartyFormationPartyListViewModel(IReadOnlyList<PartyFormationPartyViewModel> Parties);
}
