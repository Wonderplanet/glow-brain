using GLOW.Core.Domain.Helper;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.UnitTab.Domain.UseCase
{
    public class GetUnitNoticeUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IUnitEnhanceNotificationHelper UnitEnhanceNotificationHelper { get; }

        public NotificationBadge GetUnitNotification()
        {
            var userUnits = GameRepository.GetGameFetchOther().UserUnitModels;

            var isGradeUp = NotificationBadge.False;
            foreach(var userUnit in userUnits)
            {
                isGradeUp = UnitEnhanceNotificationHelper.GetUnitNotification(userUnit);
                if (isGradeUp != NotificationBadge.False) break;
            }

            return isGradeUp;
        }
    }
}
