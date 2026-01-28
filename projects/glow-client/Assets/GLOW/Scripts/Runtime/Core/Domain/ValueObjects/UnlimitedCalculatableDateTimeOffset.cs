using System;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record UnlimitedCalculableDateTimeOffset(ObscuredDateTimeOffset Value)
    {
        // TODO:TimeSpan部分でUTCの場合の考慮が必要
        // 期限チェックで無期限となるStartAtとEndAtの閾値に使用する
        public static DateTimeOffset UnlimitedStartAt { get; } = new DateTimeOffset(2020,1,1, 0,0,0,TimeSpan.Zero);
        public static DateTimeOffset UnlimitedEndAt { get; } = new DateTimeOffset(2037, 12, 31, 0, 0, 0, TimeSpan.Zero);

        public static UnlimitedCalculableDateTimeOffset UnlimitedStart { get; } =
            new UnlimitedCalculableDateTimeOffset(UnlimitedStartAt);
        public static UnlimitedCalculableDateTimeOffset UnlimitedEnd { get; } =
            new UnlimitedCalculableDateTimeOffset(UnlimitedEndAt);

        public static implicit operator DateTimeOffset(UnlimitedCalculableDateTimeOffset date) => date.Value;

        public bool IsUnlimitedStartAt => Value <= UnlimitedStartAt;
        public bool IsUnlimitedEndAt => UnlimitedEndAt <= Value;

        public static bool operator <(UnlimitedCalculableDateTimeOffset a, DateTimeOffset b)
        {
            return a.Value <= b;
        }

        public static bool operator >(UnlimitedCalculableDateTimeOffset a, DateTimeOffset b)
        {
            return a.Value > b;
        }
    }
}
