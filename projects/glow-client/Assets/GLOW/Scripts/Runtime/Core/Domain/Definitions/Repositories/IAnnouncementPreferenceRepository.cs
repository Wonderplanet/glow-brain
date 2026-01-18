using System;
using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.AnnouncementWindow;
using GLOW.Scenes.AnnouncementWindow.Domain.ValueObject;

namespace GLOW.Core.Domain.Repositories
{
    public interface IAnnouncementPreferenceRepository
    {
        Dictionary<AnnouncementId, AnnouncementLastUpdateAt> ReadAnnouncementIdAndLastUpdated { get; }
        void SetReadAnnouncementIdAndLastUpdated(Dictionary<AnnouncementId, AnnouncementLastUpdateAt> announcementIdAndLastUpdated);
        void RemoveReadAnnouncementIdAndLastUpdated(IReadOnlyList<AnnouncementId> announcementIdsToRemoveFromRead);

        DateTimeOffset AnnouncementLastDisplayDateTimeOffsetAtLogin { get; }
        void SetAnnouncementLastDisplayDateTimeOffsetAtLogin(DateTimeOffset announcementLastDisplayDateTimeOffset);

        AnnouncementLastUpdateAt InformationLastUpdated { get; }
        void SetInformationLastUpdated(AnnouncementLastUpdateAt informationLastUpdated);

        AnnouncementLastUpdateAt OperationLastUpdated { get; }
        void SetOperationLastUpdated(AnnouncementLastUpdateAt operationLastUpdated);
        
        AnnouncementLastUpdateAt ReadInformationLastUpdated { get; }
        void SetReadInformationLastUpdated(AnnouncementLastUpdateAt informationLastUpdated);

        AnnouncementLastUpdateAt ReadOperationLastUpdated { get; }
        void SetReadOperationLastUpdated(AnnouncementLastUpdateAt operationLastUpdated);
        
        AlreadyReadAnnouncementFlag AnnouncementAlreadyReadAll { get; }
        void SetAnnouncementAlreadyReadAll(AlreadyReadAnnouncementFlag announcementReadAll);
    }
}
