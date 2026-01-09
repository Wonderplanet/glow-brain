using System;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Presentation.Modules
{
    public static class AmountFormatter
    {

        public static string FormatAmount(int number)
        {
            return FormatAmountString(number);
        }

        public static string FormatAmount(long number)
        {
            return FormatAmountString(number);
        }

        static string FormatAmountString(long number)
        {
            // 1,000,000表示
            return $"{number:#,0}";
        }

        public static string FormatSecond(TimeSpan timeSpan)
        {
            // 0:00表示
            return $"{timeSpan:m\\:ss}";
        }
        
        public static string FormatSecond(RemainingTimeSpan timeSpan)
        {
            // 0:00表示
            return $"{timeSpan.Value:m\\:ss}";
        }
        
        public static string FormatSecond(int seconds)
        {
            return $"{TimeSpan.FromSeconds(seconds):m\\:ss}";
        }
    }
}
