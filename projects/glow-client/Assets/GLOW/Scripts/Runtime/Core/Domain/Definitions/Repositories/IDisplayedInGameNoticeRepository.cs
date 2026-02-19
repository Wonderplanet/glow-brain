using System;
using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.Notice;

namespace GLOW.Core.Domain.Repositories
{
    public interface IDisplayedInGameNoticeRepository
    {
        DateTimeOffset LastCheckedNoticeDateTimeOffset { get; }
        void SaveLastCheckedNoticeDateTimeOffset(DateTimeOffset lastCheckedNoticeDateTimeOffset);
        
        HashSet<NoticeId> DisplayedDailyNoticeIdHashSet { get; }
        void AddDisplayedDailyNoticeId(NoticeId noticeId);
        void DeleteDisplayedDailyNoticeIdHashSet();

        HashSet<NoticeId> DisplayedWeeklyNoticeIdHashSet { get; }
        void AddDisplayedWeeklyNoticeId(NoticeId noticeId);
        void DeleteDisplayedWeeklyNoticeIdHashSet();

        HashSet<NoticeId> DisplayedMonthlyNoticeIdHashSet { get; }
        void AddDisplayedMonthlyNoticeId(NoticeId noticeId);
        void DeleteDisplayedMonthlyNoticeIdHashSet();

        HashSet<NoticeId> DisplayedOnceNoticeIdHashSet { get; }
        void AddDisplayedOnceNoticeId(NoticeId noticeId);
    }
}