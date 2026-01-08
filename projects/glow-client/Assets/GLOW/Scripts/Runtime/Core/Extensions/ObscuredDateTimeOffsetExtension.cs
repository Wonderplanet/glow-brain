using System;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Extensions
{
    public static class ObscuredDateTimeOffsetExtension
    {
        public static ObscuredDateTimeOffset AddHours(this ObscuredDateTimeOffset obscuredDateTimeOffset, double hours)
        {
            return ((DateTimeOffset)obscuredDateTimeOffset).AddHours(hours);
        }
        
        public static ObscuredDateTimeOffset AddMinutes(this ObscuredDateTimeOffset obscuredDateTimeOffset, double minutes)
        {
            return ((DateTimeOffset)obscuredDateTimeOffset).AddMinutes(minutes);
        }
        
        public static ObscuredDateTimeOffset ToJst(this ObscuredDateTimeOffset utcNow)
        {
            return ((DateTimeOffset)utcNow).ToJst();
        }
    }
}