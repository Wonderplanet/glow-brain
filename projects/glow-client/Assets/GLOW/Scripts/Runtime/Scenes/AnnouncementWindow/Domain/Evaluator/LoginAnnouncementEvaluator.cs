using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects.AnnouncementWindow;
using Zenject;

namespace GLOW.Scenes.AnnouncementWindow.Domain.Evaluator
{
    public class LoginAnnouncementEvaluator : ILoginAnnouncementEvaluator
    {
        [Inject] IAnnouncementPreferenceRepository AnnouncementPreferenceRepository { get; }

        [Inject] IDailyResetTimeCalculator DailyResetTimeCalculator { get; }

        bool ILoginAnnouncementEvaluator.ShouldShowLoginAnnouncement(
            AnnouncementLastUpdateAt informationLastUpdatedAt,
            AnnouncementLastUpdateAt operationLastUpdatedAt)
        {
            var beforeInformationLastUpdated = AnnouncementPreferenceRepository.InformationLastUpdated;
            var beforeOperationLastUpdated = AnnouncementPreferenceRepository.OperationLastUpdated;

            if (beforeInformationLastUpdated < informationLastUpdatedAt || beforeOperationLastUpdated < operationLastUpdatedAt)
            {
                // お知らせが更新されていれば表示する
                return true;
            }
            return DailyResetTimeCalculator.IsPastDailyRefreshTime(AnnouncementPreferenceRepository.AnnouncementLastDisplayDateTimeOffsetAtLogin);
        }
    }
}
