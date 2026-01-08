using System;
using Cysharp.Text;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Core.Domain.Modules.Time
{
    public static class TimeSpanFormatter
    {
        public static string Format(TimeSpan timeSpan, string prefix, string postfix)
        {
            switch (timeSpan)
            {
                case { Days: > 0 }:
                    return ZString.Format("{0}{1}日{2}時間{3}", prefix, timeSpan.Days, timeSpan.Hours, postfix);
                case { Days: 0, Hours: > 0 }:
                    return ZString.Format("{0}{1}時間{2}", prefix, timeSpan.Hours, postfix);
                case { Days: 0, Hours: 0, Minutes: > 0 }:
                    return ZString.Format("{0}{1}分{2}", prefix, timeSpan.Minutes, postfix);
                default:
                    return ZString.Format("{0}1分未満{1}", prefix, postfix);
            }
        }

        public static string Format(TimeSpan timeSpan, string prefix, string postfix, int numberSize)
        {
            switch (timeSpan)
            {
                case { Days: > 0 }:
                    return ZString.Format(
                        "{0}<size={1}>{2}</size>日<size=22>{3}</size>時間{4}",
                        prefix,
                        numberSize,
                        timeSpan.Days,
                        timeSpan.Hours,
                        postfix);

                case { Days: 0, Hours: > 0 }:
                    return ZString.Format("{0}<size={1}>{2}</size>時間{3}", prefix, numberSize, timeSpan.Hours, postfix);

                case { Days: 0, Hours: 0, Minutes: > 0 }:
                    return ZString.Format("{0}<size={1}>{2}</size>分{3}", prefix, numberSize, timeSpan.Minutes, postfix);

                default:
                    return ZString.Format("{0}<size={1}>1</size>分未満{2}", prefix, numberSize, postfix);
            }
        }

        public static string Format(TimeSpan timeSpan)
        {
            return Format(timeSpan, "", "");
        }

        public static string FormatRemaining(TimeSpan timeSpan)
        {
            return Format(timeSpan, "残り ", "");
        }

        public static string FormatRemaining(RemainingTimeSpan timeSpan)
        {
            if (timeSpan.IsInfinity()) return "期限なし";
            
            return timeSpan.HasValue() ? FormatRemaining(timeSpan.Value) : "";
        }

        public static string FormatUntilEnd(TimeSpan timeSpan)
        {
            return Format(timeSpan, "終了まで：残り ", "");
        }

        public static string FormatUntilEnd(RemainingTimeSpan timeSpan)
        {
            return timeSpan.HasValue() ? FormatUntilEnd(timeSpan.Value) : "";
        }

        public static string FormatUntilUpdate(RemainingTimeSpan timeSpan)
        {
            return timeSpan.HasValue() ? Format(timeSpan.Value, "更新まで：残り ", "") : "";
        }

        public static string FormatUntilRelease(TimeSpan timeSpan)
        {
            return Format(timeSpan, "開放まで残り ", "");
        }

        public static string FormatUntilReleaseWithLB(TimeSpan timeSpan)
        {
            return Format(timeSpan, "開放まで残り\n", "！！");
        }

        public static string FormatUntilOpen(TimeSpan timeSpan)
        {
            return Format(timeSpan, "開催まで残り ", "");
        }

        public static string FormatUntilRecovery(TimeSpan timeSpan)
        {
            var prefix = ZString.Format("回復まで残り <color={0}>", ColorCodeTheme.TextRed);
            return Format(timeSpan, prefix, "</color>", 22);
        }

        public static string FormatUntilUnlinkable(RemainingTimeSpan timeSpan)
        {
            return timeSpan.HasValue() ? Format(timeSpan.Value, "解除可能まで残り ", "") : "";
        }

        public static string FormatUntilReset(AdGachaResetRemainingTimeSpan timeSpan)
        {
            return timeSpan.HasValue() ? Format(timeSpan.Value, "リセットまで\n残り ", "") : "";
        }

        public static string FormatUntilResetAd(AdGachaResetRemainingTimeSpan timeSpan)
        {
            return timeSpan.HasValue() ? Format(timeSpan.Value, "<color=#EE3628>視聴可能回数リセットまで 残り  ", "</color>") : "";
        }
    }
}
