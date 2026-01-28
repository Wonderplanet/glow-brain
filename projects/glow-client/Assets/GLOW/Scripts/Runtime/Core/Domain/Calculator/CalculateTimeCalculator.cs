using System;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Calculator
{
    public class CalculateTimeCalculator
    {
        public static RemainingTimeSpan GetRemainingTime(DateTimeOffset nowDateTime, DateTimeOffset endDateTime)
        {
            var result = endDateTime - nowDateTime;
            return new RemainingTimeSpan(result);
        }

        public static bool IsValidTime(DateTimeOffset nowDateTime, DateTimeOffset startDateTime,
            DateTimeOffset endDateTime)
        {
            return startDateTime <= nowDateTime && nowDateTime <= endDateTime;
        }

        public static DateTimeOffset GetNextDay(DateTimeOffset nowTime)
        {
            var addedDateTime = nowTime.AddDays(1).ToLocalTime();
            return new DateTimeOffset(addedDateTime.Year, addedDateTime.Month, addedDateTime.Day, 0, 0, 0, addedDateTime.Offset);
        }

        public static DateTimeOffset GetNextWeek(DateTimeOffset nowTime)
        {
            var currentDayOfWeek = (int)nowTime.DateTime.DayOfWeek;
            var addDay = (currentDayOfWeek == 0) ? 1 :  7 - currentDayOfWeek + 1;
            var addedDateTime = nowTime.AddDays(addDay).ToLocalTime();
            return new DateTimeOffset(addedDateTime.Year, addedDateTime.Month, addedDateTime.Day, 0, 0, 0, addedDateTime.Offset);
        }
        
        public static DateTimeOffset GetNextMonth(DateTimeOffset nowTime)
        {
            var addedDateTime = nowTime.AddMonths(1).ToLocalTime();
            return new DateTimeOffset(addedDateTime.Year, addedDateTime.Month, 1, 0, 0, 0, addedDateTime.Offset);
        }
    }
}
