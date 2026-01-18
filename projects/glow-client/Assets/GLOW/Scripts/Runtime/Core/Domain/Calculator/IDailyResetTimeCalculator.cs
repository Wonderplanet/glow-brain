using System;

namespace GLOW.Core.Domain.Calculator
{
    public interface IDailyResetTimeCalculator
    {
        TimeSpan GetRemainingTimeToDailyReset();
        TimeSpan GetRemainingTimeToWeeklyReset();
        TimeSpan GetRemainingTimeToMonthlyReset();
        bool IsPastDailyRefreshTime(DateTimeOffset beforeTime);
        bool IsPastWeeklyRefreshTime(DateTimeOffset beforeTime);
        bool IsPastMonthlyRefreshTime(DateTimeOffset beforeTime);
        DateTimeOffset GetTodayWithDailyRefreshTime();
    }
}
