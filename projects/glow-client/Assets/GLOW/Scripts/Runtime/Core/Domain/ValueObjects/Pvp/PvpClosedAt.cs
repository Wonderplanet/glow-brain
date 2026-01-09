using System;
using System.Globalization;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Pvp
{
    public record PvpClosedAt(ObscuredDateTimeOffset Value)
    {
        public static PvpClosedAt Empty { get; } = new(DateTimeOffset.MinValue);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public string ToFormattedString()
        {
            return Value.ToString("yyyy/MM/dd HH:mm", CultureInfo.InvariantCulture);
        }
    }
}
