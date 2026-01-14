using System;

namespace GLOW.Core.Extensions
{
    public static class DateTimeOffsetExtension
    {
        public static DateTimeOffset ToJst(this DateTimeOffset utcNow)
        {
            //TimeZoneInfoはOSによってIDが異なるので使わない
            var jpOffset = TimeSpan.FromHours(9);
            
            return utcNow.ToOffset(jpOffset);
        }
    }
}