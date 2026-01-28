using System;
using GLOW.Core.Data.Data.Announcement;
using GLOW.Core.Domain.Models.Announcement;
using GLOW.Core.Domain.ValueObjects.AnnouncementWindow;

namespace GLOW.Core.Data.Translators
{
    public class AnnouncementLastUpdatedModelTranslator
    {
        public static AnnouncementLastUpdatedModel ToAnnouncementLastUpdatedModel(
            AnnouncementLastUpdatedData announcementLastUpdatedData)
        {
            var informationIos = announcementLastUpdatedData.InformationIos ?? DateTimeOffset.MinValue;
            var operationIos = announcementLastUpdatedData.OperationIos ?? DateTimeOffset.MinValue;
            var informationAndroid = announcementLastUpdatedData.InformationAndroid ?? DateTimeOffset.MinValue;
            var operationAndroid = announcementLastUpdatedData.OperationAndroid ?? DateTimeOffset.MinValue;
            
            return new AnnouncementLastUpdatedModel(
                informationIos == DateTimeOffset.MinValue ? 
                    AnnouncementLastUpdateAt.Empty : 
                    new AnnouncementLastUpdateAt(informationIos),
                operationIos == DateTimeOffset.MinValue ? 
                    AnnouncementLastUpdateAt.Empty : 
                    new AnnouncementLastUpdateAt(operationIos),
                informationAndroid == DateTimeOffset.MinValue ? 
                    AnnouncementLastUpdateAt.Empty : 
                    new AnnouncementLastUpdateAt(informationAndroid),
                operationAndroid == DateTimeOffset.MinValue ? 
                    AnnouncementLastUpdateAt.Empty : 
                    new AnnouncementLastUpdateAt(operationAndroid));
        }
    }
}