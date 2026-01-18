using System;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Repositories;
using Zenject;

namespace GLOW.Scenes.Notice.Domain.Initializer
{
    public class DisplayedInGameNoticeRecordResetter : IDisplayedInGameNoticeRecordResetter
    {
        [Inject] IDisplayedInGameNoticeRepository DisplayedInGameNoticeRepository { get; }
        [Inject] IDailyResetTimeCalculator DailyResetTimeCalculator { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        
        public void ResetDisplayedInGameNoticeRecord()
        {
            var lastCheckedNoticeDateTimeOffset = DisplayedInGameNoticeRepository.LastCheckedNoticeDateTimeOffset;
            if (lastCheckedNoticeDateTimeOffset == DateTimeOffset.MinValue)
            {
                DisplayedInGameNoticeRepository.SaveLastCheckedNoticeDateTimeOffset(TimeProvider.Now);
                return;
            }
            
            TryDeleteDisplayedDailyInGameNotice(lastCheckedNoticeDateTimeOffset);
            
            TryDeleteDisplayedWeeklyInGameNotice(lastCheckedNoticeDateTimeOffset);
            
            TryDeleteDisplayedMonthlyInGameNotice(lastCheckedNoticeDateTimeOffset);
            
            // 確認した時の日時を保存
            DisplayedInGameNoticeRepository.SaveLastCheckedNoticeDateTimeOffset(TimeProvider.Now);
        }

        void TryDeleteDisplayedDailyInGameNotice(DateTimeOffset nowTime)
        {
            if (!DailyResetTimeCalculator.IsPastDailyRefreshTime(nowTime)) return;
            
            DisplayedInGameNoticeRepository.DeleteDisplayedDailyNoticeIdHashSet();
        }
        
        void TryDeleteDisplayedWeeklyInGameNotice(DateTimeOffset nowTime)
        {
            if (!DailyResetTimeCalculator.IsPastWeeklyRefreshTime(nowTime)) return;
            
            DisplayedInGameNoticeRepository.DeleteDisplayedWeeklyNoticeIdHashSet();
        }
        
        void TryDeleteDisplayedMonthlyInGameNotice(DateTimeOffset nowTime)
        {
            if (!DailyResetTimeCalculator.IsPastMonthlyRefreshTime(nowTime)) return;
            
            DisplayedInGameNoticeRepository.DeleteDisplayedMonthlyNoticeIdHashSet();
        }
    }
}