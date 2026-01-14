using System;
using GLOW.Core.Domain.ValueObjects.AnnouncementWindow;
using GLOW.Scenes.AnnouncementWindow.Domain.Enum;

namespace GLOW.Scenes.AnnouncementWindow.Domain.Applier
{
    public interface IAnnouncementDateTimeApplier
    {
        void UpdateAnnouncementLastUpdatedDateTimeAtLogin(
            DateTimeOffset loginDateTime,
            AnnouncementLastUpdateAt informationLastUpdateAt,
            AnnouncementLastUpdateAt operationLastUpdateAt,
            AnnouncementDisplayMeansType displayMeansType);
    }
}