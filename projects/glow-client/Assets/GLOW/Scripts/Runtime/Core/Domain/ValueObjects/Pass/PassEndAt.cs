using System;
using System.Globalization;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Pass
{
    public record PassEndAt(ObscuredDateTimeOffset Value)
    {
        public static PassEndAt Empty { get; } = new(DateTimeOffset.MinValue);

        public static RemainingTimeSpan operator -(PassEndAt endAt, DateTimeOffset currentTime)
        {
            return new RemainingTimeSpan(endAt.Value - currentTime);
        }

        public static bool operator < (PassEndAt endAt, DateTimeOffset currentTime)
        {
            return endAt.Value < currentTime;
        }
        public static bool operator > (PassEndAt endAt, DateTimeOffset currentTime)
        {
            return endAt.Value > currentTime;
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public string ToFormattedString()
        {
            return Value.ToString("yyyy/MM/dd HH:mm:ss", CultureInfo.InvariantCulture);
        }
    }
}
