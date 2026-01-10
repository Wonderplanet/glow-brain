using System;
using System.Globalization;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.AnnouncementWindow
{
    public record AnnouncementStartAt(ObscuredDateTimeOffset Value)
    {
        public static AnnouncementStartAt Empty { get; } = new(DateTimeOffset.MinValue);
        
        public AnnouncementLastUpdateAt ToLastUpdateAt()
        {
            return new AnnouncementLastUpdateAt(Value);
        }
        
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