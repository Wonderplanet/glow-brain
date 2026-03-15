using System.Collections.Generic;

namespace GLOW.Scenes.ArtworkFormation.Presentation.ViewModels
{
    public record ArtworkFormationPartyViewModel(
        List<ArtworkFormationPartyCellViewModel> CellViewModels);
}