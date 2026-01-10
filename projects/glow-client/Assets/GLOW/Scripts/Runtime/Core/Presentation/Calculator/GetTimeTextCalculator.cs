using System;

namespace GLOW.Core.Presentation.Calculator
{
    public class GetTimeTextCalculator
    {
        public static string GetShopRemainingTimeText(TimeSpan termTime)
        {
            var text = "残り";
            if (termTime.TotalDays >= 1)
            {
                text += $"{termTime.Days}日 ";
            }
            text += termTime.ToString(@"hh':'mm");
            return text;
        }
    }
}