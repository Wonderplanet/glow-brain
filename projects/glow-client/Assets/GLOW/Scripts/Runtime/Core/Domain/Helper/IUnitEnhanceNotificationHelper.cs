using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Helper
{
    public interface IUnitEnhanceNotificationHelper
    {
        NotificationBadge GetUnitNotification(UserUnitModel userUnit);
        NotificationBadge GetUnitGradeUpNotification(UserUnitModel userUnit);
    }
}
