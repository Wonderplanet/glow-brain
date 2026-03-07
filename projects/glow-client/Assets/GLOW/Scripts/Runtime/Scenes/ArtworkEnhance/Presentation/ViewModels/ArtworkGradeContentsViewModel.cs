using System.Collections.Generic;

namespace GLOW.Scenes.ArtworkEnhance.Presentation.ViewModels
{
    public record ArtworkGradeContentsViewModel(IReadOnlyList<ArtworkGradeContentCellViewModel> CellViewModels);
}
