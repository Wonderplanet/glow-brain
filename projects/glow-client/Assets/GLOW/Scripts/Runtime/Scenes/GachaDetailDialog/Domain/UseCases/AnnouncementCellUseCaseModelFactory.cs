using GLOW.Core.Domain.AnnouncementWindow;
using GLOW.Core.Domain.Models.Announcement;
using GLOW.Core.Domain.Repositories;
using GLOW.Scenes.AnnouncementWindow.Domain.Model;
using Zenject;

namespace GLOW.Scenes.GachaDetailDialog.Domain.UseCases
{
    public class AnnouncementCellUseCaseModelFactory : IAnnouncementCellUseCaseModelFactory
    {
        [Inject] IAnnouncementPreferenceRepository AnnouncementPreferenceRepository { get; }
        
        AnnouncementCellUseCaseModel IAnnouncementCellUseCaseModelFactory.Create(AnnouncementModel announcementModel)
        {
            var cellType = announcementModel.BannerUrl.IsEmpty() ? AnnouncementCellType.Text : AnnouncementCellType.Banner;
            var tabType =
                announcementModel.AnnouncementCategory is AnnouncementCategory.Bug or AnnouncementCategory.Important
                    ? AnnouncementTabType.Operation
                    : AnnouncementTabType.Event;

            var readAnnouncementIdAndLastUpdated = AnnouncementPreferenceRepository.ReadAnnouncementIdAndLastUpdated;

            var isRead = false;
            if (readAnnouncementIdAndLastUpdated.TryGetValue(announcementModel.AnnouncementId, out var lastUpdatedAt))
            {
                // 最終更新日と一致&既にIDが記録されていたら既読
                isRead = announcementModel.LastUpdatedAt == lastUpdatedAt;
            }

            return new AnnouncementCellUseCaseModel(
                announcementModel.AnnouncementId,
                tabType,
                cellType,
                announcementModel.AnnouncementCategory,
                announcementModel.LastUpdatedAt,
                announcementModel.Title,
                announcementModel.BannerUrl,
                announcementModel.Status,
                announcementModel.ContentsUrl,
                announcementModel.StartAt,
                announcementModel.EndAt,
                isRead
            );
        }
    }
}