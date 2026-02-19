using System.Linq;
using GLOW.Core.Data.Data.Announcement;
using GLOW.Core.Domain.Models.Announcement;
using GLOW.Core.Domain.ValueObjects.AnnouncementWindow;

namespace GLOW.Core.Data.Translators
{
    public class AnnouncementResultModelTranslator
    {
        public static AnnouncementResultModel ToAnnouncementResultModel(
            AnnouncementResultData announcementResultData)
        {
            var models = announcementResultData.AnnouncementData.Select(data => 
                new AnnouncementModel(
                    new AnnouncementId(data.InformationId),
                    new AnnouncementOsType(data.OsType),
                    new AnnouncementLastUpdateAt(data.LastUpdatedAt),
                    new AnnouncementCreatedAt(data.CreatedAt),
                    new AnnouncementContentsUrl(data.ContentsUrl),
                    new AnnouncementTitle(data.Title),
                    new AnnouncementBannerUrl(data.BannerUrl),
                    data.AnnouncementCategory,
                    data.Status,
                    new AnnouncementStartAt(data.StartAt),
                    new AnnouncementEndAt(data.EndAt))
            ).ToList();

            return new AnnouncementResultModel(models);
        }
    }
}