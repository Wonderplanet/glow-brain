using System;
using GLOW.Core.Domain.Modules.Time;

namespace GLOW.Core.Domain.ValueObjects.Gacha
{
    public record AdGachaResetRemainingText(string Value)
    {
        public string Value { get; } = Value;
        public static AdGachaResetRemainingText Empty { get; } = new("");

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public static AdGachaResetRemainingText GetAdGachaResetRemainingText(AdGachaResetRemainingTimeSpan timeSpan)
        {
            AdGachaResetRemainingText remainingTimeText;

            if (timeSpan.Value == TimeSpan.MinValue)
            {
                return Empty;
            }

            remainingTimeText = new AdGachaResetRemainingText(TimeSpanFormatter.FormatUntilReset(timeSpan));

            return remainingTimeText;
        }
    }
}
