using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.AnnouncementWindow.Domain.Model
{
    public record FetchAnnouncementListModel(
        IReadOnlyList<AnnouncementCellUseCaseModel> Announcements, 
        HookedPatternUrl HookedPatternUrlInAnnouncements);
}