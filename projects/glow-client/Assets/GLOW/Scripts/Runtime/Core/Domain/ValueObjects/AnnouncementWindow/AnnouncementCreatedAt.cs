using System;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.AnnouncementWindow
{
    public record AnnouncementCreatedAt(ObscuredDateTimeOffset Value)
    {
        public static AnnouncementCreatedAt Empty { get; } = new(DateTimeOffset.MinValue);
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}