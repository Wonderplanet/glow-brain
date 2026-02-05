using System;
using System.Globalization;

namespace GLOW.Core.Domain.Modules.Time
{
    public static class DateTimeOffsetFormatter
    {
        public static string FormatDateTime(DateTimeOffset dateTimeOffset)
        {
            return dateTimeOffset.ToString("yyyy-MM-dd HH:mm", CultureInfo.InvariantCulture);
        }
        
        public static string FormatDate(DateTimeOffset dateTimeOffset)
        {
            return dateTimeOffset.ToString("yyyy-MM-dd", CultureInfo.InvariantCulture);
        }
    }
}