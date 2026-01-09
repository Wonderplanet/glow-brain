using System;
using System.Globalization;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Pvp
{
    public record PvpEndAt(ObscuredDateTimeOffset Value)
    {
        public static PvpEndAt Empty { get; } = new(DateTimeOffset.MinValue);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public string ToFormattedString()
        {
            return Value.ToString("yyyy/MM/dd HH:mm", CultureInfo.InvariantCulture);
        }

        public static TimeSpan operator -(DateTimeOffset dateTime, PvpEndAt endAt)
        {
            return endAt.Value - dateTime;
        }

        public static TimeSpan operator -(PvpEndAt endAt, DateTimeOffset dateTime)
        {
            return endAt.Value - dateTime;
        }


    }
}
