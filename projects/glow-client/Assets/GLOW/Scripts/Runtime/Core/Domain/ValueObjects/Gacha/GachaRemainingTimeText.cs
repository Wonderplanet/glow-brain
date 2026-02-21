using System;
using GLOW.Core.Domain.Modules.Time;

namespace GLOW.Core.Domain.ValueObjects.Gacha
{
    public record GachaRemainingTimeText(string Value)
    {
        public string Value { get; } = Value;
        public static GachaRemainingTimeText Empty { get; } = new ("");

        public bool IsEmpty()
        {
            return Value == Empty.Value;
        }

        public static GachaRemainingTimeText CreateRemainingTimeText(
            DateTimeOffset endAt,
            DateTimeOffset now, 
            GachaExpireAt userGachaExpireAt)
        {
            var remainingTimeText = Empty;

            // 期間無制限かつ期間限定ではない場合は残り時間を空で返す
            if (endAt >= UnlimitedCalculableDateTimeOffset.UnlimitedEndAt && userGachaExpireAt.IsEmpty())
            {
                return remainingTimeText;
            }
            
            TimeSpan timeSpan;
            if (userGachaExpireAt.IsEmpty())
            {
                timeSpan = endAt - now;
            }
            else
            {
                // 期間限定の場合は短い方を使う
                var minEndTime = endAt < userGachaExpireAt.Value ? endAt : (DateTimeOffset)userGachaExpireAt.Value;
                timeSpan = minEndTime - now;
            }

            return new GachaRemainingTimeText(TimeSpanFormatter.FormatUntilEnd(timeSpan));
        }
    }
}
