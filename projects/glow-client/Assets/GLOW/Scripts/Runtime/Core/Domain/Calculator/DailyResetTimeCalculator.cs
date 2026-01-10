using System;
using GLOW.Core.Domain.Repositories;
using Zenject;
using GLOW.Core.Extensions;

namespace GLOW.Core.Domain.Calculator
{
    // 汎用的に使うならQuestContentTopから移動も視野
    public class DailyResetTimeCalculator : IDailyResetTimeCalculator
    {
        [Inject] ITimeProvider TimeProvider { get; }

        // TODO: 日跨ぎの時間はMstConfigからもらう
        const int DailyResetHour = 4;
        const DayOfWeek WeeklyResetDayOfWeek = DayOfWeek.Monday;

        TimeSpan IDailyResetTimeCalculator.GetRemainingTimeToDailyReset()
        {
            // NOTE: 現在時刻から次(翌日or当日)のリセット時間を取得して、TimeSpanを返す
            var now = TimeProvider.Now.ToJst();
            var dailyRefreshTime = new DateTimeOffset(now.Year, now.Month, now.Day, DailyResetHour, 0, 0, now.Offset);
            if (now.Hour < DailyResetHour)
            {
                return dailyRefreshTime - now;
            }
            else if (now == dailyRefreshTime) //4時ピッタリ
            {
                return TimeSpan.Zero;
            }
            else
            {
                var todayDailyRefreshTime = dailyRefreshTime.AddDays(1);
                return todayDailyRefreshTime - now;
            }
        }

        TimeSpan IDailyResetTimeCalculator.GetRemainingTimeToWeeklyReset()
        {
            // JSTにして日跨ぎ時間分引く
            var jstNow = TimeProvider.Now.ToJst();
            var shiftedJstNow = jstNow.AddHours(-DailyResetHour);

            // 日跨ぎ時間分引いた状態の日付で週跨ぎの基準日を計算する
            var nextWeekStartDate = CalculateTimeCalculator.GetNextWeek(shiftedJstNow);

            // 基準日の4時が週跨ぎ時間
            var weeklyRefreshTime = new DateTimeOffset(
                nextWeekStartDate.Year,
                nextWeekStartDate.Month,
                nextWeekStartDate.Day,
                DailyResetHour,
                0,
                0,
                nextWeekStartDate.Offset);

            // JSTにして日跨ぎ時間分引く(後の計算に向けて条件を揃える)
            var jstWeeklyStartDate = weeklyRefreshTime.ToJst();
            var shiftedWeeklyRefreshTime = jstWeeklyStartDate.AddHours(-DailyResetHour);

            // 残り時間を計算
            var remainingTime = shiftedWeeklyRefreshTime - shiftedJstNow;

            // 残り時間がちょうど7日間(1週間)の時は週跨ぎ時間上なので0で返す
            if (remainingTime == TimeSpan.FromDays(7))
            {
                return TimeSpan.Zero;
            }

            return remainingTime;
        }

        TimeSpan IDailyResetTimeCalculator.GetRemainingTimeToMonthlyReset()
        {
            // JSTにして日跨ぎ時間分引く
            var jstNow = TimeProvider.Now.ToJst();
            var shiftedJstNow = jstNow.AddHours(-DailyResetHour);

            // 日跨ぎ時間分引いた状態の日付で月跨ぎの基準日を計算する
            var nextMonthStartDate = CalculateTimeCalculator.GetNextMonth(shiftedJstNow);

            // 基準日の4時が月跨ぎ時間
            var monthlyRefreshTime = new DateTimeOffset(
                nextMonthStartDate.Year,
                nextMonthStartDate.Month,
                nextMonthStartDate.Day,
                DailyResetHour,
                0,
                0,
                nextMonthStartDate.Offset);

            // JSTにして日跨ぎ時間分引く(後の計算に向けて条件を揃える)
            var jstMonthlyStartDate = monthlyRefreshTime.ToJst();
            var shiftedMonthlyRefreshTime = jstMonthlyStartDate.AddHours(-DailyResetHour);

            // 残り時間を計算
            var remainingTime = shiftedMonthlyRefreshTime - shiftedJstNow;

            // 月の最終日を取得
            var daysInMonth = DateTime.DaysInMonth(shiftedJstNow.Year, shiftedJstNow.Month);

            // 残り時間がちょうど1ヶ月の時は月跨ぎ時間上なので0で返す
            if (remainingTime == TimeSpan.FromDays(daysInMonth))
            {
                return TimeSpan.Zero;
            }

            return remainingTime;
        }

        bool IDailyResetTimeCalculator.IsPastDailyRefreshTime(DateTimeOffset beforeTime)
        {
            // beforeTimeがdefault(minValue)の場合、jstBeforeTime.AddHours()でエラーになるのでtrue
            if (beforeTime == default)
            {
                return true;
            }

            var jstNow = TimeProvider.Now.ToJst();
            var jstBeforeTime = beforeTime.ToJst();

            var shiftedJstNow = jstNow.AddHours(-DailyResetHour);
            var shiftedJstBeforeTime = jstBeforeTime.AddHours(-DailyResetHour);

            return shiftedJstNow.Date > shiftedJstBeforeTime.Date;
        }

        bool IDailyResetTimeCalculator.IsPastWeeklyRefreshTime(DateTimeOffset beforeTime)
        {
            // beforeTimeがdefault(minValue)の場合、jstBeforeTime.AddHours()でエラーになるのでtrue
            if (beforeTime == default)
            {
                return true;
            }

            var jstNow = TimeProvider.Now.ToJst();
            var jstBeforeTime = beforeTime.ToJst();

            var shiftedJstNow = jstNow.AddHours(-DailyResetHour);
            var shiftedJstBeforeTime = jstBeforeTime.AddHours(-DailyResetHour);

            // 7日以上経過している場合は確実に週を跨いでいるのでtrue
            if (shiftedJstNow.Date - shiftedJstBeforeTime.Date >= TimeSpan.FromDays(7))
            {
                return true;
            }

            // 次の月曜日の日付を計算
            var nextWeekByShiftedJstBeforeTime = CalculateTimeCalculator.GetNextWeek(shiftedJstBeforeTime);
            var nextWeekByShiftedJstTime = CalculateTimeCalculator.GetNextWeek(shiftedJstNow);

            // 不一致の場合は週を跨いでいる
            return nextWeekByShiftedJstBeforeTime != nextWeekByShiftedJstTime;
        }

        bool IDailyResetTimeCalculator.IsPastMonthlyRefreshTime(DateTimeOffset beforeTime)
        {
            // beforeTimeがdefault(minValue)の場合、jstBeforeTime.AddHours()でエラーになるのでtrue
            if (beforeTime == default)
            {
                return true;
            }

            var jstNow = TimeProvider.Now.ToJst();
            var jstBeforeTime = beforeTime.ToJst();

            var shiftedJstNow = jstNow.AddHours(-DailyResetHour);
            var shiftedJstBeforeTime = jstBeforeTime.AddHours(-DailyResetHour);

            // 年を跨いでいる場合はtrue(12月 -> 1月)
            if (shiftedJstBeforeTime.Year < shiftedJstNow.Year)
            {
                return true;
            }

            // 月を跨いでいる場合はtrue
            if (shiftedJstBeforeTime.Month < shiftedJstNow.Month)
            {
                return true;
            }

            return false;
        }

        DateTimeOffset IDailyResetTimeCalculator.GetTodayWithDailyRefreshTime()
        {
            bool isNowTimeUtc = TimeProvider.Now.Offset == TimeSpan.Zero;
            var nowJst = TimeProvider.Now.ToJst();
            bool isJstAndUtcDayDifferent = nowJst.Day != TimeProvider.Now.Day;
            var dailyRefreshTime = new DateTimeOffset(
                nowJst.Year,
                nowJst.Month,
                nowJst.Day,
                DailyResetHour,
                0,
                0,
                nowJst.Offset);

            DateTimeOffset todayJst;
            if (nowJst.Hour < DailyResetHour)
            {
                // JSTの日付がUTCの日付と異なる場合は、JSTの日付が翌日になるので、JSTの日付を1日戻す
                todayJst = isJstAndUtcDayDifferent ? nowJst.AddDays(-1) : nowJst;
            }
            else
            {
                // 入力値がUTCの場合は、日付を1日進める
                todayJst = isNowTimeUtc ? dailyRefreshTime : dailyRefreshTime.AddDays(1);
            }

            return new DateTimeOffset(
                todayJst.Year,
                todayJst.Month,
                todayJst.Day,
                0,
                0,
                0,
                TimeSpan.Zero);
        }
    }
}
