using System;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.AnnouncementWindow
{
    public record AnnouncementEndAt(ObscuredDateTimeOffset Value)
    {
        public static AnnouncementEndAt Empty { get; } = new(DateTimeOffset.MinValue);
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}