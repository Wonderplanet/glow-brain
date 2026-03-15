using System;
using WonderPlanet.CultureSupporter.Time;

namespace WPFramework.Modules.Date
{
    public static class DatePivotChecker
    {
        // 初期値はJST(+9:00)の00:00
        public static DatePivotSettings Settings { get; set; } = new (9,0,0);

        /// <summary>
        /// 指定時間が基準時間を跨いでいるかどうか
        /// </summary>
        /// <param name="currentTime">現在時間</param>
        /// <param name="targetDate">指定時間</param>
        /// <returns>true:跨いでいる、false:跨いでない</returns>
        public static bool IsDateCrossingPivot(DateTimeOffset currentTime, DateTimeOffset? targetDate)
        {
            // NOTE: 現在時間を取得
            var nowDate = currentTime;

            if (nowDate <= targetDate)
            {
                // NOTE: 指定時間が現在時間を越えることはない
                return false;
            }

            // NOTE: 基準時間を算出するため、設定タイムゾーン基準に変換する（日付を正常に取得する為）
            var timeZoneSpan = TimeSpan.FromHours(Settings.TimeZoneOffset);
            nowDate = nowDate.ToOffset(timeZoneSpan);
            var pivotDate = new DateTimeOffset(nowDate.Year, nowDate.Month, nowDate.Day, Settings.HourOffset, Settings.MinuteOffset, 0, timeZoneSpan);
            if (pivotDate > nowDate)
            {
                // NOTE: 基準時間が現在時間より超えているので、基準時間を１日前（現在時間より前）に補正する
                pivotDate = pivotDate.AddDays(-1);
            }

            // NOTE: 基準時間を指定時間が超えているか？
            if (pivotDate <= targetDate)
            {
                // NOTE: 「基準時間　<=　指定時間　<　現在時間」の関係にあるので基準時間を跨いでいないことになる
                return false;
            }

            // NOTE: 「指定時間　<　基準時間　<　現在時間」の関係にあるので基準時間を跨いだことになる
            return true;
        }
    }
}
