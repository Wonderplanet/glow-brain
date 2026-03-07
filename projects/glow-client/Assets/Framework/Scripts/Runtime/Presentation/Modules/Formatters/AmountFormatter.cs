using System;
using System.Globalization;

namespace WPFramework.Presentation.Modules
{
    public static class AmountFormatter
    {
        const int DefaultPrecision = 1;
        static readonly string[] Suffixes = { "", "K", "M", "G", "T", "P" };

        public static string FormatAmount(int number, int precision = DefaultPrecision)
        {
            return FormatAmount((decimal)number, precision);
        }

        public static string FormatAmount(long number, int precision = DefaultPrecision)
        {
            return FormatAmount((decimal)number, precision);
        }

        public static string FormatAmount(decimal number, int precision = DefaultPrecision)
        {
            var divisor = 1m;
            var index = 0;

            for (var i = 0; i < Suffixes.Length - 1; ++i)
            {
                if (number < 1000 * divisor)
                {
                    break;
                }

                divisor *= 1000;
                index++;
            }

            return number < 10_000 ?
                number.ToString(CultureInfo.InvariantCulture) :
                RoundToTwoDecimalPlaces(number / divisor, precision) + Suffixes[index];
        }

        static decimal RoundToTwoDecimalPlaces(decimal number, int digits)
        {
            return decimal.Round(number, digits, MidpointRounding.AwayFromZero);
        }

        public static string FormatAmountWithCommas(long number)
        {
            return number.ToString("N0", CultureInfo.InvariantCulture);
        }
    }
}
