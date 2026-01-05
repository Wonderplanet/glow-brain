using System;
using GLOW.Core.Domain.ValueObjects;
using UnityEngine;
using WondlerPlanet.LocalNotification;
using Zenject;

namespace GLOW.Core.Domain.Modules.LocalNotification
{
    public class LocalNotifier : ILocalNotifier
    {
        [Inject] ILocalNotificationCenter LocalNotificationCenter { get; }

        public LocalNotificationIdentifier AddSchedule(string title, string message, DateTimeOffset fireTime)
        {
            var notificationData = new NotificationData()
            {
                Title = title,
                Message = message,
                FireTime = fireTime.LocalDateTime,
                ShowInForeground = false,
                SmallIcon = "local_icon_small",
                // LargeIcon = "local_icon_large",
                Color = new Color32(238,54,50,255)
            };
#if UNITY_ANDROID || UNITY_IOS
            return new LocalNotificationIdentifier(LocalNotificationCenter.AddSchedule(notificationData));
#else
            return LocalNotificationIdentifier.Empty;
#endif
        }

        public void RemoveSchedule(LocalNotificationIdentifier identifier)
        {
            LocalNotificationCenter.RemoveSchedule(identifier.Value);
        }

        public void RemoveAllSchedule()
        {
            LocalNotificationCenter.RemoveAllSchedule();
        }
    }
}
