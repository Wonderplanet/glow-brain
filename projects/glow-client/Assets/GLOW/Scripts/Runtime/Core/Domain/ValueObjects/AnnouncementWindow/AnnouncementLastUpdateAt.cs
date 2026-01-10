using System;
using System.Globalization;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.AnnouncementWindow
{
    public record AnnouncementLastUpdateAt(ObscuredDateTimeOffset Value) : IComparable<AnnouncementLastUpdateAt>
    {
        public static AnnouncementLastUpdateAt Empty { get; } = new(DateTimeOffset.MinValue);

        public static AnnouncementLastUpdateAt Max(AnnouncementLastUpdateAt a, AnnouncementLastUpdateAt b)
        {
            return a.Value > b.Value ? a : b;
        }
        
        public static bool operator >(AnnouncementLastUpdateAt a, AnnouncementLastUpdateAt b)
        {
            return a.Value > b.Value;
        }

        public static bool operator <(AnnouncementLastUpdateAt a, AnnouncementLastUpdateAt b)
        {
            return a.Value < b.Value;
        }
        
        public static bool operator >=(AnnouncementLastUpdateAt a, AnnouncementLastUpdateAt b)
        {
            return a.Value >= b.Value;
        }

        public static bool operator <=(AnnouncementLastUpdateAt a, AnnouncementLastUpdateAt b)
        {
            return a.Value <= b.Value;
        }

        public override string ToString()
        {
            return Value.ToString(CultureInfo.InvariantCulture);
        }

        public int CompareTo(AnnouncementLastUpdateAt other)
        {
            return Value.CompareTo(other.Value);
        }
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
        
        public bool IsEqual(DateTimeOffset time)
        {
            return Value == time;
        }
    }
}