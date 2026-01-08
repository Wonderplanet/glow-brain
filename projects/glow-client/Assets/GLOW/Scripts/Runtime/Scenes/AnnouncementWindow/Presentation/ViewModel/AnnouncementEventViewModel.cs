using System.Collections.Generic;

namespace GLOW.Scenes.AnnouncementWindow.Presentation.ViewModel
{
    public record AnnouncementEventViewModel(
        IReadOnlyList<AnnouncementCellViewModel> InformationEventCellViewModels);
}