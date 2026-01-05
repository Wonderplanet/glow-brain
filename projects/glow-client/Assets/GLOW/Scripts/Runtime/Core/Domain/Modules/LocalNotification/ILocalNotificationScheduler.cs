using System;
using GLOW.Core.Constants.LocalNotification;

namespace GLOW.Core.Domain.Modules.LocalNotification
{
    public interface ILocalNotificationScheduler
    {
        void Initialize();
        void RefreshIdleIncentiveSchedule();
        void RefreshDailyMissionSchedule();
        void RefreshRemainCoinQuestSchedule();
        void RefreshRemainPvPSchedule();
        void RefreshRemainAdventBattleCountSchedule();
        void RefreshRemainAdGachaSchedule();
        void RefreshLoginSchedule();
        void RefreshBeginnerMissionSchedule();
        void RefreshTutorialSchedule();
        void RemoveAllSchedules();
        void RefreshAllSchedules();

#if GLOW_DEBUG
        public void DebugRefreshSchedule(
            LocalNotificationType type,
            string message,
            DateTimeOffset fireTime);
#endif //GLOW_DEBUG
    }
}
