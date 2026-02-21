using GLOW.Core.Domain.Models.Announcement;
using GLOW.Scenes.AnnouncementWindow.Domain.Model;

namespace GLOW.Scenes.GachaDetailDialog.Domain.UseCases
{
    public interface IAnnouncementCellUseCaseModelFactory
    {
        AnnouncementCellUseCaseModel Create(AnnouncementModel announcementModel);
    }
}