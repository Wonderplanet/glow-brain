using System.Collections.Generic;

namespace GLOW.Scenes.AnnouncementWindow.Presentation.ViewModel
{
    public record AnnouncementOperationViewModel(
        IReadOnlyList<AnnouncementCellViewModel> InformationOperationCellViewModels);
}