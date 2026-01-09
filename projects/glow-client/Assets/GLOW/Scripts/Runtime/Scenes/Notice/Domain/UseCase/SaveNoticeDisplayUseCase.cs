using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects.Notice;
using Zenject;

namespace GLOW.Scenes.Notice.Domain.UseCase
{
    public class SaveNoticeDisplayUseCase
    {
        [Inject] IDisplayedInGameNoticeRepository DisplayedInGameNoticeRepository { get; }

        public void SaveInGameNoticeDisplay(NoticeId noticeId, IgnDisplayFrequencyType displayFrequencyType)
        {
            switch(displayFrequencyType)
            {
                case IgnDisplayFrequencyType.Always:
                    break;
                case IgnDisplayFrequencyType.Daily:
                    DisplayedInGameNoticeRepository.AddDisplayedDailyNoticeId(noticeId);
                    break;
                case IgnDisplayFrequencyType.Weekly:
                    DisplayedInGameNoticeRepository.AddDisplayedWeeklyNoticeId(noticeId);
                    break;
                case IgnDisplayFrequencyType.Monthly:
                    DisplayedInGameNoticeRepository.AddDisplayedMonthlyNoticeId(noticeId);
                    break;
                case IgnDisplayFrequencyType.Once:
                    DisplayedInGameNoticeRepository.AddDisplayedOnceNoticeId(noticeId);
                    break;
            }
        }
    }
}
