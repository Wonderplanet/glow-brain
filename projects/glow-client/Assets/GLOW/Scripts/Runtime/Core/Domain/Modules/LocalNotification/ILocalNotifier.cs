using System;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Modules.LocalNotification
{
    public interface ILocalNotifier
    {
        LocalNotificationIdentifier AddSchedule(string title, string message, DateTimeOffset fireTime);
        void RemoveSchedule(LocalNotificationIdentifier identifier);
        void RemoveAllSchedule();
    }
}
